<?php

declare(strict_types=1);

return [
    'default_driver' => env('VIP_SEARCH_DRIVER', 'database'),
    'index_prefix' => env('VIP_SEARCH_INDEX_PREFIX', 'vip'),
    'hybrid_retrieval_enabled' => env('VIP_SEARCH_HYBRID_RETRIEVAL_ENABLED', false),
];
