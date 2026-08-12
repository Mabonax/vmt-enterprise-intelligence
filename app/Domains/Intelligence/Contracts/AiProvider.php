<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Contracts;

use App\Domains\Intelligence\DTOs\ChatRequest;
use App\Domains\Intelligence\DTOs\ChatResponse;

interface AiProvider
{
    public function key(): string;

    public function chat(ChatRequest $request): ChatResponse;

    public function stream(ChatRequest $request): iterable;

    public function embeddings(array $input): array;

    public function models(): array;

    public function health(): array;

    public function estimateTokens(array|string $input): int;
}
