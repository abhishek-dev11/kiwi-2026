<?php

namespace App\Jobs\Concerns;

use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

// For supplier import jobs that need to look at the WHOLE file, not just their own chunk
// (e.g. a variation whose parent row is in an earlier chunk). Used by ProductImportJob
// subclasses — relies on their $path, normalizeHeaders() and mapRowToHeaders().
trait ScansImportFile
{
    // Every data row of the file, in file order, as a header-keyed array (CSV or XLSX).
    protected function scanRows(): \Generator
    {
        if (!Storage::disk('local')->exists($this->path)) {
            return;
        }

        $filePath = Storage::disk('local')->path($this->path);

        if (strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) === 'csv') {
            $handle = fopen($filePath, 'r');
            if (!$handle) return;

            $headers = $this->normalizeHeaders((array) fgetcsv($handle, 0, ','));
            while (($raw = fgetcsv($handle, 0, ',')) !== false) {
                yield $this->mapRowToHeaders($headers, $raw);
            }
            fclose($handle);
            return;
        }

        $reader = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filePath);
        $sheet       = $spreadsheet->getActiveSheet();
        $last        = $sheet->getHighestDataColumn();
        $headers     = $this->normalizeHeaders($sheet->rangeToArray("A1:{$last}1", null, true, false)[0] ?? []);

        foreach ($sheet->rangeToArray("A2:{$last}" . $sheet->getHighestDataRow(), null, true, false) as $raw) {
            yield $this->mapRowToHeaders($headers, $raw);
        }

        $spreadsheet->disconnectWorksheets();
    }
}
