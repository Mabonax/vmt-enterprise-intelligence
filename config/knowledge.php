<?php

declare(strict_types=1);

return [
    'storage_disk' => env('VIP_KNOWLEDGE_DISK', 'local'),
    'chunking' => [
        'default_strategy' => env('VIP_KNOWLEDGE_CHUNK_STRATEGY', 'semantic'),
        'default_size' => (int) env('VIP_KNOWLEDGE_CHUNK_SIZE', 800),
        'default_overlap' => (int) env('VIP_KNOWLEDGE_CHUNK_OVERLAP', 120),
    ],
];
