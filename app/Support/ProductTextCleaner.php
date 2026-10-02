<?php

namespace App\Support;

// Cleans supplier product titles/descriptions during import (every supplier, CSV / XLSX /
// API — see ProductImportJob::groupRows()) and in the products:clean-text repair command.
//
// Supplier data mostly arrives with *mojibake*: UTF-8 text that was decoded as
// Windows-1252 somewhere upstream, so "–" shows as "â€“", "’" as "â€™", "Café" as
// "CafÃ©", "®" as "Â®" — sometimes garbled twice ("Ã¢â‚¬â€" for "—"). Those sequences
// are reversed back to the character the supplier meant rather than stripped, so real
// punctuation and accented letters survive. Only genuinely invisible junk is removed
// (control characters, zero-width characters, byte-order marks, U+FFFD).
class ProductTextCleaner
{
    // Characters Windows-1252 maps to bytes 0x80-0x9F (undefined bytes 0x81, 0x8D, 0x8F,
    // 0x90, 0x9D pass through as the matching C1 control, as most decoders do).
    private const CP1252_HIGH = [
        0x20AC => 0x80, 0x201A => 0x82, 0x0192 => 0x83, 0x201E => 0x84, 0x2026 => 0x85,
        0x2020 => 0x86, 0x2021 => 0x87, 0x02C6 => 0x88, 0x2030 => 0x89, 0x0160 => 0x8A,
        0x2039 => 0x8B, 0x0152 => 0x8C, 0x017D => 0x8E, 0x2018 => 0x91, 0x2019 => 0x92,
        0x201C => 0x93, 0x201D => 0x94, 0x2022 => 0x95, 0x2013 => 0x96, 0x2014 => 0x97,
        0x02DC => 0x98, 0x2122 => 0x99, 0x0161 => 0x9A, 0x203A => 0x9B, 0x0153 => 0x9C,
        0x017E => 0x9E, 0x0178 => 0x9F,
    ];

    // A run of 2+ characters that each map to a single Windows-1252 byte >= 0x80 — the
    // shape every mojibake sequence has.
    private const MOJIBAKE_RUN = '/[\x{80}-\x{FF}\x{152}\x{153}\x{160}\x{161}\x{178}\x{17D}\x{17E}\x{192}\x{2C6}\x{2DC}\x{2013}\x{2014}\x{2018}-\x{201A}\x{201C}-\x{201E}\x{2020}-\x{2022}\x{2026}\x{2030}\x{2039}\x{203A}\x{20AC}\x{2122}]{2,}/u';

    public static function title(?string $text): string
    {
        $text = self::common((string) $text);

        // Titles are plain text, escaped on output — "&amp;" would show literally.
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = str_replace("\u{00A0}", ' ', $text);

        return trim(preg_replace('/\s+/u', ' ', $text));
    }

    public static function description(?string $text): string
    {
        $text = self::common((string) $text);

        // Description is output as raw HTML ({!! !!}). HTML descriptions keep their markup
        // and entities as-is; a plain-text one has its entities decoded and its line
        // breaks turned into paragraphs, otherwise the browser runs every line together.
        if (preg_match('/<\/?[a-z][^>]*>/i', $text)) {
            return trim($text);
        }

        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = str_replace("\u{00A0}", ' ', $text);
        $text = preg_replace('/[ \t]+/u', ' ', $text);
        $text = preg_replace('/ *\n */', "\n", trim($text));

        if ($text === '') {
            return '';
        }

        $paragraphs = preg_split('/\n{2,}/', $text);

        return implode("\n", array_map(
            fn($p) => '<p>' . nl2br(htmlspecialchars($p, ENT_QUOTES, 'UTF-8'), false) . '</p>',
            $paragraphs
        ));
    }

    // Shared by title and description.
    private static function common(string $text): string
    {
        if ($text === '') {
            return '';
        }

        if (!mb_check_encoding($text, 'UTF-8')) {
            $text = mb_convert_encoding($text, 'UTF-8', 'Windows-1252');
        }

        // Literal escape sequences left in the text by a supplier export ("sizes.\nVersatile"
        // stored as backslash + n). Only \r \n \t are treated as escapes — any other
        // backslash is left alone.
        $text = str_replace(['\r\n', '\n', '\r', '\t'], ["\n", "\n", "\n", ' '], $text);
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        $text = self::fixMojibake($text);

        // Invisible junk: BOM / zero-width characters, U+FFFD, and control characters
        // other than newline/tab (incl. stray C1 controls left from broken encodings).
        $text = preg_replace('/[\x{FEFF}\x{200B}-\x{200D}\x{2060}\x{FFFD}]/u', '', $text);
        $text = preg_replace('/[\x{00}-\x{08}\x{0B}\x{0C}\x{0E}-\x{1F}\x{7F}-\x{9F}]/u', '', $text);

        return $text;
    }

    // Lead bytes real mojibake starts with, as the Windows-1252 character they show as:
    //   2-byte  C2 "Â" / C3 "Ã" (U+0080-00FF: ® © ° é ×…), C5 "Å" (Œ œ Š š…),
    //           CB "Ë" (˚ ˜ ˆ)
    //   3-byte  E2 "â" (U+2000-2FFF: – — ’ “ ” … € ™), EF "ï" (BOM, U+FFFD)
    // Other leads are left alone so genuine text such as "CAFÉ’S" (É’ = C9 92, which
    // happens to be valid UTF-8 for "ɒ") is never "repaired" into something else.
    private const MOJIBAKE_LEADS = [0xC2 => 1, 0xC3 => 1, 0xC5 => 1, 0xCB => 1, 0xE2 => 2, 0xEF => 2];

    // Reverses Windows-1252 mojibake within each run of suspicious characters, sequence
    // by sequence: a lead byte followed by the right number of continuation bytes is
    // decoded; anything else in the run keeps its original character. Correctly encoded
    // text elsewhere is untouched. Repeats to undo text that was garbled twice.
    public static function fixMojibake(string $text): string
    {
        for ($pass = 0; $pass < 3; $pass++) {
            $fixed = preg_replace_callback(self::MOJIBAKE_RUN, function ($m) {
                $chars = mb_str_split($m[0]);
                $bytes = array_map(fn($c) => self::CP1252_HIGH[mb_ord($c)] ?? mb_ord($c), $chars);
                $out   = '';

                for ($i = 0, $n = count($bytes); $i < $n; $i++) {
                    $need = self::MOJIBAKE_LEADS[$bytes[$i]] ?? 0;

                    if ($need > 0 && $i + $need < $n) {
                        $seq = chr($bytes[$i]);
                        for ($k = 1; $k <= $need; $k++) {
                            $seq .= chr($bytes[$i + $k]);
                        }

                        if (mb_check_encoding($seq, 'UTF-8')) {
                            $out .= $seq;
                            $i   += $need;
                            continue;
                        }
                    }

                    $out .= $chars[$i];
                }

                return $out;
            }, $text);

            if ($fixed === null || $fixed === $text) {
                break;
            }
            $text = $fixed;
        }

        return $text;
    }
}
