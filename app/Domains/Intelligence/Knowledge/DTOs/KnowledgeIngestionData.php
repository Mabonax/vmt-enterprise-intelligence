<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Knowledge\DTOs;

final readonly class KnowledgeIngestionData
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public string $title,
        public string $content,
        public string $sourceType,
        public string $mimeType,
        public array $metadata = [],
    ) {}
}
