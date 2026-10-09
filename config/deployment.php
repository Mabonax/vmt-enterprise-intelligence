<?php

declare(strict_types=1);

return [
    // The gateway is delivered alongside VMT enterprise software, not as a public SaaS.
    'mode' => env('VMT_DEPLOYMENT_MODE', 'dedicated'),
    'public_registration' => filter_var(env('VMT_PUBLIC_REGISTRATION', false), FILTER_VALIDATE_BOOLEAN),
    'commercial_console_enabled' => filter_var(env('VMT_COMMERCIAL_CONSOLE_ENABLED', false), FILTER_VALIDATE_BOOLEAN),
    'managed_by' => env('VMT_DEPLOYMENT_OPERATOR', 'VMT'),
];
