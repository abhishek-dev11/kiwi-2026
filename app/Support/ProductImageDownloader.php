<?php

namespace App\Support;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

// Downloads product images into public/uploads/Product for the file imports and the
// products:fetch-images command.
//  - URLs are fetched concurrently (prefetch()), not one blocking request per product.
//  - The local filename is derived from the URL, so a URL that is already on disk is
//    never downloaded twice, across products, chunks and imports.
//  - A host that keeps failing to connect (down / blocked) is skipped for a cool-down
//    period, so an unreachable supplier site can't stall every product of an import.
//  - Failures are classified: "transient" (connection error, timeout, 5xx, 429) may
//    succeed later, "permanent" (404, not an image, ...) won't.
class ProductImageDownloader
{
    const STORAGE_PATH       = 'uploads/Product';
    const POOL_SIZE          = 8;    // concurrent downloads — more makes slow links time out on connect
    const HOST_TRIP_AFTER    = 3;    // consecutive fully-failed batches before a host is skipped
    const HOST_COOLDOWN_MIN  = 10;
    const MIN_IMAGE_BYTES    = 100;

    // url => local filename | null
    protected array $results = [];
    // url => true when the failure may be temporary
    protected array $transient = [];
    protected array $hostStreak = [];
    protected array $hostDown = [];

    public function download(string $url): ?string
    {
        $this->prefetch([$url]);

        return $this->results[$url] ?? null;
    }

    // True when the last download() / prefetch() of $url failed for a reason that may go
    // away on its own (host unreachable, timeout, 5xx) — worth retrying later.
    public function failedTransiently(string $url): bool
    {
        return ($this->results[$url] ?? null) === null && ($this->transient[$url] ?? false);
    }

    public function prefetch(array $urls): void
    {
        $dir = public_path(self::STORAGE_PATH);
        if (!is_dir($dir)) @mkdir($dir, 0755, true);

        $pending = [];
        foreach (array_unique($urls) as $url) {
            $url = trim((string) $url);
            if ($url === '' || array_key_exists($url, $this->results)) continue;

            if (!preg_match('#^https?://#i', $url)) {
                $this->results[$url] = null;
                continue;
            }

            // Already downloaded by an earlier import of the same URL.
            $existing = glob($dir . '/' . $this->baseName($url) . '.*');
            if (!empty($existing)) {
                $this->results[$url] = basename($existing[0]);
                continue;
            }

            if ($this->isHostDown($url)) {
                $this->results[$url]   = null;
                $this->transient[$url] = true;
                continue;
            }

            $pending[] = $url;
        }

        foreach (array_chunk($pending, self::POOL_SIZE) as $batch) {
            // The host may have tripped during an earlier batch of this call.
            $batch = array_values(array_filter($batch, function ($url) {
                if (!$this->isHostDown($url)) return true;
                $this->results[$url]   = null;
                $this->transient[$url] = true;
                return false;
            }));
            if (empty($batch)) continue;

            try {
                $responses = Http::pool(function ($pool) use ($batch) {
                    foreach ($batch as $url) {
                        $pool->as($url)
                            ->timeout(60)
                            ->connectTimeout(15)
                            ->withUserAgent('Mozilla/5.0 (compatible; ProductImporter/1.0)')
                            ->get($this->fetchUrl($url));
                    }
                });
            } catch (\Throwable $e) {
                \Log::warning('ImageDownloader: pool failed: ' . $e->getMessage());
                foreach ($batch as $url) {
                    $this->results[$url]   = null;
                    $this->transient[$url] = true;
                }
                continue;
            }

            // A host counts as failing only when NOTHING in a batch came back — a few slow or
            // timed-out requests among successful ones don't mean the host is down.
            $reached = [];
            foreach ($batch as $url) {
                $response = $responses[$url] ?? null;
                $this->handleResponse($url, $response);
                $host = $this->hostKey($url);
                $reached[$host] = ($reached[$host] ?? false) || $response instanceof Response;
            }
            foreach ($reached as $hostKey => $ok) {
                $this->noteHostKey($hostKey, $ok, $batch[0]);
            }
        }
    }

    // For callers that fetch an image themselves (category images): skip a host that has
    // been failing, and report each result so the shared circuit breaker keeps count.
    public function hostIsSkipped(string $url): bool
    {
        return $this->isHostDown($url);
    }

    public function reportHostResult(string $url, bool $reachable): void
    {
        $this->noteHostKey($this->hostKey($url), $reachable, $url);
    }

    protected function handleResponse(string $url, $response): void
    {
        $this->results[$url] = null;

        if (!$response instanceof Response) {
            // Connection error / timeout.
            $this->transient[$url] = true;
            \Log::warning("ImageDownloader: {$url} unreachable: " . ($response instanceof \Throwable ? $response->getMessage() : 'no response'));
            return;
        }

        if (!$response->successful()) {
            $status = $response->status();
            $this->transient[$url] = $status >= 500 || in_array($status, [408, 429], true);
            \Log::warning("ImageDownloader: HTTP {$status} — {$url}");
            return;
        }

        $data = $response->body();
        $info = strlen($data) >= self::MIN_IMAGE_BYTES ? @getimagesizefromstring($data) : false;
        $ext  = $info ? (image_type_to_extension($info[2], false) ?: null) : null;

        if (!$ext) {
            $this->transient[$url] = false;
            \Log::warning("ImageDownloader: not a valid image — {$url}");
            return;
        }

        $fileName = $this->baseName($url) . '.' . ($ext === 'jpeg' ? 'jpg' : $ext);
        $path     = public_path(self::STORAGE_PATH . '/' . $fileName);

        // Write to a temp file first so a crash can't leave a truncated image that the
        // "already on disk" check would later accept.
        $tmp = $path . '.part';
        if (@file_put_contents($tmp, $data) === false || !@rename($tmp, $path)) {
            @unlink($tmp);
            $this->transient[$url] = true;
            \Log::warning("ImageDownloader: could not write {$fileName}");
            return;
        }

        $this->results[$url] = $fileName;
    }

    // The URL actually requested. A Dropbox share link (…?dl=0) answers with an HTML preview
    // page, not the picture; raw=1 makes Dropbox return the file itself. Results, filenames
    // and the URL stored in the database all stay keyed on the original link.
    // A Google Drive file link (…/file/d/{id}/view) likewise answers with Drive's viewer
    // page; uc?export=download returns the file itself.
    protected function fetchUrl(string $url): string
    {
        if (($driveId = $this->googleDriveFileId($url)) !== null) {
            return 'https://drive.google.com/uc?export=download&id=' . rawurlencode($driveId);
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        if ($host !== 'dropbox.com' && !str_ends_with($host, '.dropbox.com')) {
            return $url;
        }

        $parts = parse_url($url);
        parse_str($parts['query'] ?? '', $query);
        unset($query['dl']);
        $query['raw'] = '1';

        return ($parts['scheme'] ?? 'https') . '://' . $parts['host'] . ($parts['path'] ?? '') . '?' . http_build_query($query);
    }

    // The file id of a Google Drive file link — …/file/d/{id}/… or …?id={id} — or null.
    // Folder links (…/drive/folders/…) aren't files and return null.
    protected function googleDriveFileId(string $url): ?string
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        if ($host !== 'drive.google.com' && $host !== 'docs.google.com') {
            return null;
        }

        if (preg_match('#/file/d/([A-Za-z0-9_-]+)#', $url, $m)) {
            return $m[1];
        }

        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

        return isset($query['id']) && preg_match('/^[A-Za-z0-9_-]+$/', $query['id']) ? $query['id'] : null;
    }

    protected function baseName(string $url): string
    {
        // Drive links all end in "/view" — name the file after its Drive id instead.
        $driveId = $this->googleDriveFileId($url);
        $name    = $driveId !== null
            ? 'gdrive-' . $driveId
            : pathinfo((string) parse_url($url, PHP_URL_PATH), PATHINFO_FILENAME);
        $name = trim(preg_replace('/[^A-Za-z0-9_-]+/', '-', $name), '-') ?: 'product';

        return substr($name, 0, 80) . '_' . substr(md5($url), 0, 10);
    }

    // ── Host circuit breaker ───────────────────────────────
    protected function hostKey(string $url): string
    {
        return 'import_image_host_down_' . md5(strtolower((string) parse_url($url, PHP_URL_HOST)));
    }

    protected function isHostDown(string $url): bool
    {
        $key = $this->hostKey($url);

        return $this->hostDown[$key] ??= Cache::has($key);
    }

    protected function noteHostKey(string $key, bool $reachable, string $sampleUrl): void
    {
        if ($reachable) {
            $this->hostStreak[$key] = 0;
            return;
        }

        $this->hostStreak[$key] = ($this->hostStreak[$key] ?? 0) + 1;

        if ($this->hostStreak[$key] >= self::HOST_TRIP_AFTER && empty($this->hostDown[$key])) {
            $this->hostDown[$key] = true;
            Cache::put($key, true, now()->addMinutes(self::HOST_COOLDOWN_MIN));
            \Log::warning('ImageDownloader: host ' . parse_url($sampleUrl, PHP_URL_HOST) . ' unreachable — skipping its images for ' . self::HOST_COOLDOWN_MIN . ' min');
        }
    }
}
