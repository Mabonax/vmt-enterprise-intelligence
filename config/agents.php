<?php

declare(strict_types=1);

return [
    'default_memory_scope' => env('VIP_AGENTS_DEFAULT_MEMORY_SCOPE', 'session'),
    'tool_calling_enabled' => env('VIP_AGENTS_TOOL_CALLING_ENABLED', true),
    'max_parallel_jobs' => (int) env('VIP_AGENTS_MAX_PARALLEL_JOBS', 5),
];
