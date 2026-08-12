<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Knowledge\Services;

class CitationService
{
    /**
     * @return list<array<string, mixed>>
     */
    public function fromDocument(string $documentId, string $title, array $chunks = []): array
    {
        return [
            [
                'title' => $title,
                'url' => null,
                'excerpt' => $chunks[0]['content'] ?? null,
                'metadata' => ['document_id' => $documentId],
            ],
        ];
    }
}
