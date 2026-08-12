<?php

declare(strict_types=1);

return [
    'currency' => env('VIP_BILLING_CURRENCY', 'USD'),
    'grace_period_days' => (int) env('VIP_BILLING_GRACE_PERIOD_DAYS', 7),
    'usage_capture_enabled' => env('VIP_BILLING_USAGE_CAPTURE_ENABLED', true),
];
