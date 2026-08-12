<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Services;

use App\Domains\Intelligence\DTOs\Citation;

class CitationService
{
    /**
     * @param list<array<string, mixed>> $citations
     * @return list<Citation>
     */
    public function normalize(array $citations): array
    {
        return array_map(
            static fn (array $citation): Citation => new Citation(
                title: (string) ($citation['title'] ?? 'Untitled source'),
                url: isset($citation['url']) ? (string) $citation['url'] : null,
                excerpt: isset($citation['excerpt']) ? (string) $citation['excerpt'] : null,
                metadata: is_array($citation['metadata'] ?? null) ? $citation['metadata'] : [],
            ),
            $citations,
        );
    }
}
