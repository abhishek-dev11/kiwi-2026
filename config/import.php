<?php

return [
    // true  -> each chunk is a real queued job (QUEUE_CONNECTION, "imports" queue) that a
    //          worker runs, with retries and its own timeout. Start one with:
    //            php artisan queue:work --queue=imports --timeout=900 --memory=512 --tries=3
    // false -> legacy: chunks run one after another inside the web request that started
    //          the import (after the response is sent). Not suitable for large files.
    'use_queue'  => env('IMPORT_USE_QUEUE', true),
    'queue'      => env('IMPORT_QUEUE', 'imports'),

    // Rows per queued chunk job.
    'chunk_size' => (int) env('IMPORT_CHUNK_SIZE', 250),
];
