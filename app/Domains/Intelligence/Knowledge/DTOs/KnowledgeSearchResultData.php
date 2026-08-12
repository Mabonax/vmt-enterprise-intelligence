<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Knowledge\DTOs;

final readonly class KnowledgeSearchResultData
{
    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public string $type,
        public string $id,
        public string $title,
        public float $score,
        public array $payload = [],
        public array $metadata = [],
    ) {}
}
