<?php

declare(strict_types=1);

return [
    'driver' => env('VIP_MEMORY_DRIVER', 'database'),
    'default_ttl_minutes' => (int) env('VIP_MEMORY_DEFAULT_TTL_MINUTES', 10080),
    'scopes' => ['conversation', 'session', 'organization', 'agent'],
];
