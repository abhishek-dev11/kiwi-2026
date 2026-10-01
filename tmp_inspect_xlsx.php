<?php

require __DIR__ . '/vendor/autoload.php';

$file = 'C:/Users/91831/Downloads/products-2026-06-30.xlsx';
$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($file);
$reader->setReadDataOnly(true);
$sheet = $reader->load($file)->getActiveSheet();

foreach ($sheet->rangeToArray('A1:AZ8', null, true, true, true) as $i => $row) {
    $row = array_filter($row, fn($value) => $value !== null && $value !== '');
    echo 'ROW ' . $i . ': ' . json_encode($row, JSON_UNESCAPED_UNICODE) . PHP_EOL;
}
