<?php

declare(strict_types=1);

return [
    'default' => env('VIP_PROVIDER_DEFAULT', 'ollama'),
    'fallback' => env('VIP_PROVIDER_FALLBACK'),
    'catalog' => [
        'llama' => ['label' => 'Llama', 'enabled' => env('VIP_PROVIDER_LLAMA_ENABLED', false)],
        'openai' => ['label' => 'OpenAI', 'enabled' => env('VIP_PROVIDER_OPENAI_ENABLED', false)],
        'claude' => ['label' => 'Claude', 'enabled' => env('VIP_PROVIDER_CLAUDE_ENABLED', false)],
        'gemini' => ['label' => 'Gemini', 'enabled' => env('VIP_PROVIDER_GEMINI_ENABLED', false)],
        'mistral' => ['label' => 'Mistral', 'enabled' => env('VIP_PROVIDER_MISTRAL_ENABLED', false)],
        'deepseek' => ['label' => 'DeepSeek', 'enabled' => env('VIP_PROVIDER_DEEPSEEK_ENABLED', false)],
        'azure-openai' => ['label' => 'Azure OpenAI', 'enabled' => env('VIP_PROVIDER_AZURE_OPENAI_ENABLED', false)],
        'ollama' => [
            'label' => 'Ollama',
            'enabled' => env('VIP_PROVIDER_OLLAMA_ENABLED', true),
            'local' => true,
            'endpoint' => env('AI_PROVIDER_OLLAMA_ENDPOINT', 'http://localhost:11434/api'),
        ],
    ],
];
