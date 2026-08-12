<?php

declare(strict_types=1);

return [
    'discovery_enabled' => env('VIP_TOOL_DISCOVERY_ENABLED', true),
    'cache_ttl_seconds' => (int) env('VIP_TOOL_DISCOVERY_CACHE_TTL_SECONDS', 300),
    'examples' => [
        'SearchPatients',
        'FindBeneficiaries',
        'GenerateProposal',
        'CreateMeeting',
        'SearchProjects',
        'GenerateInvoice',
    ],
];
