<?php

return [
    'runtime_write' => [
        'enabled' => env('BUILDER_RUNTIME_WRITE_ENABLED', false),
        'max_files_per_execution' => env('BUILDER_RUNTIME_WRITE_MAX_FILES', 25),
        'max_total_bytes_per_execution' => env('BUILDER_RUNTIME_WRITE_MAX_BYTES', 5242880),
    ],
];
