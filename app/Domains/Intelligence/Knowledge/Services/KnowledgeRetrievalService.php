<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Knowledge\Services;

use App\Domains\Intelligence\Knowledge\DTOs\KnowledgeSearchQueryData;

class KnowledgeRetrievalService
{
    public function __construct(
        private readonly SemanticSearchService $search,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function retrieveForPrompt(string $prompt, array $filters = [], int $limit = 5, string $workspace = 'intelligence'): array
    {
        $results = $this->search->search(new KnowledgeSearchQueryData(
            query: $prompt,
            filters: $filters,
            limit: $limit,
            workspace: $workspace,
        ));

        return [
            'results' => array_map(fn ($result): array => [
                'type' => $result->type,
                'id' => $result->id,
                'title' => $result->title,
                'score' => $result->score,
                'payload' => $result->payload,
            ], $results),
            'summary' => collect($results)->take(3)->map(fn ($result): string => $result->title)->implode('; '),
        ];
    }
}
