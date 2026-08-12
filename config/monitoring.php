<?php

declare(strict_types=1);

return [
    'capture_gpu_usage' => env('VIP_MONITORING_CAPTURE_GPU_USAGE', false),
    'capture_queue_health' => env('VIP_MONITORING_CAPTURE_QUEUE_HEALTH', true),
    'snapshot_interval_seconds' => (int) env('VIP_MONITORING_SNAPSHOT_INTERVAL_SECONDS', 60),
];
