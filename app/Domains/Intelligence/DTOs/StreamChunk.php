<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\DTOs;

final readonly class StreamChunk
{
    public function __construct(
        public string $event,
        public array $payload = [],
    ) {}
}
