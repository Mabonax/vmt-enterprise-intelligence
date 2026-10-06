<?php

declare(strict_types=1);

return [
    'default_provider' => env('VIP_GATEWAY_DEFAULT_PROVIDER', 'ollama'),
    'streaming_enabled' => env('VIP_GATEWAY_STREAMING_ENABLED', true),
    'retry_attempts' => (int) env('VIP_GATEWAY_RETRY_ATTEMPTS', 2),
    'request_timeout' => (int) env('AI_GATEWAY_REQUEST_TIMEOUT', 30),
    'async_timeout' => (int) env('AI_GATEWAY_ASYNC_TIMEOUT', 300),
    'audit_enabled' => (bool) env('AI_GATEWAY_AUDIT_ENABLED', true),
    'signing_required' => (bool) env('AI_GATEWAY_SIGNING_REQUIRED', true),
    'cloud_providers_enabled' => (bool) env('AI_CLOUD_PROVIDERS_ENABLED', false),
    'allow_stub_providers' => (bool) env('AI_ALLOW_STUB_PROVIDERS', false),
    'provider_allowlist' => array_values(array_filter(array_map('trim', explode(',', (string) env('AI_PROVIDER_ALLOWLIST', 'ollama'))))),
    'local_providers' => ['ollama', 'lmstudio', 'local-openai-compatible', 'llama-cpp', 'vllm'],
    'stub_providers' => ['lmstudio', 'openai', 'anthropic', 'gemini'],
    'security' => [
        'replay_window_seconds' => (int) env('AI_GATEWAY_REPLAY_WINDOW', 300),
    ],
    'capabilities' => [
        'chat',
        'search',
        'translation',
        'summarise',
        'report',
        'classify',
        'action',
        'knowledge',
        'admin',
        'metrics',
    ],
];
