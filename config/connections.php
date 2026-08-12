<?php

declare(strict_types=1);

return [
    'default_rate_limit_per_minute' => (int) env('VIP_CONNECTIONS_RATE_LIMIT_PER_MINUTE', 60),
    'future_mcp_enabled' => env('VIP_CONNECTIONS_MCP_ENABLED', false),
    'log_retention_days' => (int) env('VIP_CONNECTIONS_LOG_RETENTION_DAYS', 90),
];
