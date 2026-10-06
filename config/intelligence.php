<?php

declare(strict_types=1);

return [
    'default_provider' => env('AI_PROVIDER', 'ollama'),
    'default_model' => env('AI_MODEL', 'llama3.2:3b'),
    'default_agent' => env('AI_DEFAULT_AGENT', ''),
    'timeout' => (int) env('AI_TIMEOUT', 30),
    'streaming' => [
        'enabled' => (bool) env('AI_STREAMING', false),
        'buffered_chunks' => (bool) env('AI_STREAMING_BUFFERED', true),
    ],
    'token_limits' => [
        'max_context' => (int) env('AI_MAX_CONTEXT', 16000),
        'max_output' => (int) env('AI_MAX_OUTPUT', 2000),
    ],
    'citations' => [
        'enabled' => true,
        'include_excerpts' => true,
    ],
    'tool_execution' => [
        'enabled' => (bool) env('AI_TOOL_EXECUTION', true),
    ],
    'tool_runtime' => [
        'enabled' => (bool) env('AI_TOOL_EXECUTION', true),
        'auto_discover' => true,
        'default_timeout_seconds' => (int) env('AI_TOOL_TIMEOUT', 10),
    ],
    'agent_runtime' => [
        'max_iterations' => (int) env('AI_AGENT_MAX_ITERATIONS', 5),
        'verification_threshold' => (float) env('AI_VERIFICATION_THRESHOLD', 0.75),
        'multi_agent' => [
            'enabled' => (bool) env('AI_MULTI_AGENT_ENABLED', true),
            'default_execution_mode' => env('AI_MULTI_AGENT_EXECUTION_MODE', 'queued'),
            'default_approval_role' => env('AI_MULTI_AGENT_APPROVAL_ROLE', 'manager'),
            'monitoring_window' => (int) env('AI_MULTI_AGENT_MONITORING_WINDOW', 25),
        ],
    ],
    'memory' => [
        'enabled' => true,
        'default_visibility' => 'private',
        'default_limit' => 8,
    ],
    'knowledge' => [
        'enabled' => (bool) env('AI_KNOWLEDGE_ENABLED', true),
        'retrieval' => [
            'enabled' => (bool) env('AI_KNOWLEDGE_RETRIEVAL', true),
            'default_limit' => (int) env('AI_KNOWLEDGE_RETRIEVAL_LIMIT', 5),
            'workspace_overrides' => [
                'intelligence' => 5,
            ],
        ],
        'chunking' => [
            'default_strategy' => env('AI_KNOWLEDGE_CHUNKING', 'paragraph'),
        ],
        'embeddings' => [
            'provider' => env('AI_KNOWLEDGE_EMBEDDINGS_PROVIDER', 'ollama'),
            'model' => env('AI_KNOWLEDGE_EMBEDDINGS_MODEL', 'embeddinggemma'),
        ],
    ],
    'prompt_registry' => [
        'default_category' => 'general',
    ],
    'model_routing' => [
        'fallback' => [
            'provider' => env('AI_FALLBACK_PROVIDER', 'ollama'),
            'model' => env('AI_FALLBACK_MODEL', 'llama3.2:3b'),
        ],
    ],
    'approval' => [
        'administrator_override' => true,
    ],
    'workflow_runtime' => [
        'allow_rollback' => true,
        'approval_checkpoints' => true,
    ],
    'usage' => [
        'log' => (bool) env('AI_LOG_USAGE', true),
    ],
    'providers' => [
        'openai' => ['enabled' => (bool) env('AI_PROVIDER_OPENAI_ENABLED', false)],
        'ollama' => [
            'enabled' => (bool) env('AI_PROVIDER_OLLAMA_ENABLED', true),
            'endpoint' => env('AI_PROVIDER_OLLAMA_ENDPOINT', 'http://localhost:11434/api'),
            'timeout' => (int) env('AI_PROVIDER_OLLAMA_TIMEOUT', 30),
            'connect_timeout' => (int) env('AI_PROVIDER_OLLAMA_CONNECT_TIMEOUT', 5),
            'retry_attempts' => (int) env('AI_PROVIDER_OLLAMA_RETRY_ATTEMPTS', 1),
            'retry_sleep_milliseconds' => (int) env('AI_PROVIDER_OLLAMA_RETRY_SLEEP_MS', 200),
            'keep_alive' => env('AI_PROVIDER_OLLAMA_KEEP_ALIVE', '5m'),
            'embedding_model' => env('AI_PROVIDER_OLLAMA_EMBEDDING_MODEL', 'embeddinggemma'),
            'api_key' => env('AI_PROVIDER_OLLAMA_API_KEY', ''),
        ],
        'anthropic' => ['enabled' => (bool) env('AI_PROVIDER_ANTHROPIC_ENABLED', false)],
        'gemini' => ['enabled' => (bool) env('AI_PROVIDER_GEMINI_ENABLED', false)],
        'lmstudio' => ['enabled' => (bool) env('AI_PROVIDER_LMSTUDIO_ENABLED', false)],
    ],
    'discovered' => [
        'providers' => [],
        'agents' => [],
        'tools' => [],
    ],
];
