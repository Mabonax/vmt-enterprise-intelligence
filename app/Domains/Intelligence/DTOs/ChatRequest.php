<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\DTOs;

use App\Domains\Intelligence\Enums\ProviderType;

final readonly class ChatRequest
{
    /**
     * @param list<ChatMessage> $messages
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public ProviderType $provider,
        public string $model,
        public array $messages,
        public array $metadata = [],
    ) {}
}
