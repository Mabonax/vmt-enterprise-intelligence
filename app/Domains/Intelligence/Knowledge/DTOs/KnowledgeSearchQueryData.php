<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Knowledge\DTOs;

final readonly class KnowledgeSearchQueryData
{
    /**
     * @param array<string, mixed> $filters
     */
    public function __construct(
        public string $query,
        public array $filters = [],
        public int $limit = 8,
        public string $workspace = 'intelligence',
    ) {}
}
