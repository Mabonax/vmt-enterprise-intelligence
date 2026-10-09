<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Knowledge\Services;

use App\Domains\Intelligence\Knowledge\DTOs\KnowledgeSearchQueryData;
use App\Domains\Intelligence\Knowledge\DTOs\KnowledgeSearchResultData;
use App\Domains\Intelligence\Knowledge\Models\KnowledgeChunk;
use App\Domains\Intelligence\Knowledge\Models\KnowledgeDocument;
use App\Domains\Intelligence\Knowledge\Models\KnowledgeEmbedding;
use App\Domains\Intelligence\Knowledge\Models\KnowledgeMemory;
use App\Domains\Intelligence\Knowledge\Models\KnowledgeSearchLog;

class SemanticSearchService
{
    public function __construct(
        private readonly EmbeddingService $embeddings,
        private readonly KnowledgeGraphService $graph,
    ) {}

    /**
     * @return list<KnowledgeSearchResultData>
     */
    public function search(KnowledgeSearchQueryData $query): array
    {
        $startedAt = microtime(true);
        $needleVector = $this->embeddings->generate($query->query)['vector'];
        // ERP-supplied organization context is mandatory for cross-system retrieval.
        // Fail closed: untagged or privately scoped records must never enter ERP prompts.
        $organizationId = $query->filters['organization_id'] ?? null;
        if (is_string($organizationId) && $organizationId !== '') {
            $documents = KnowledgeDocument::query()
                ->where('metadata->organization_id', $organizationId)
                ->where('visibility', 'organization')
                ->limit(50)->get();
            $memories = KnowledgeMemory::query()
                ->where(function ($builder) use ($organizationId): void {
                    $builder->where('tenant_id', $organizationId)
                        ->orWhere('metadata->organization_id', $organizationId);
                })
                ->where('visibility', 'organization')
                ->limit(50)->get();
        } else {
            // Unscoped searches cannot silently gain access to tenant data.
            $documents = collect();
            $memories = collect();
        }
        $results = [];

        foreach ($documents as $document) {
            $chunk = KnowledgeChunk::query()->where('knowledge_document_id', $document->id)->orderBy('chunk_order')->first();
            $embedding = $chunk === null ? null : KnowledgeEmbedding::query()->where('knowledge_chunk_id', $chunk->id)->first();
            $textScore = $this->keywordScore($query->query, $document->title.' '.$document->summary.' '.$document->content);
            $vectorScore = $embedding === null ? 0.0 : $this->embeddings->cosineSimilarity($needleVector, $embedding->vector ?? []);
            $graphDensity = count($this->graph->traverse('document', $document->id)) * 0.01;
            $score = round(($textScore * 0.5) + ($vectorScore * 0.4) + $graphDensity, 4);

            if ($score <= 0) {
                continue;
            }

            $results[] = new KnowledgeSearchResultData(
                type: 'document',
                id: $document->id,
                title: $document->title,
                score: $score,
                payload: ['summary' => $document->summary],
                metadata: ['mime_type' => $document->mime_type],
            );
        }

        foreach ($memories as $memory) {
            $textScore = $this->keywordScore($query->query, $memory->title.' '.$memory->summary.' '.$memory->content);
            $vectorScore = $this->embeddings->cosineSimilarity($needleVector, $memory->embedding ?? []);
            $score = round(($textScore * 0.6) + ($vectorScore * 0.4), 4);

            if ($score <= 0) {
                continue;
            }

            $results[] = new KnowledgeSearchResultData(
                type: 'memory',
                id: $memory->id,
                title: $memory->title,
                score: $score,
                payload: ['summary' => $memory->summary],
                metadata: ['memory_type' => $memory->memory_type->value],
            );
        }

        usort($results, fn ($a, $b) => $b->score <=> $a->score);
        $results = array_slice($results, 0, $query->limit);

        KnowledgeSearchLog::query()->create([
            'user_id' => null,
            'query' => $query->query,
            'workspace' => $query->workspace,
            'latency_ms' => (int) ((microtime(true) - $startedAt) * 1000),
            'result_count' => count($results),
            'filters' => $query->filters,
            'metadata' => [],
        ]);

        return $results;
    }

    private function keywordScore(string $needle, string $haystack): float
    {
        $terms = collect(preg_split('/\s+/', strtolower(trim($needle))) ?: [])
            ->filter(fn (string $term): bool => $term !== '')
            ->values();

        if ($terms->isEmpty()) {
            return 0.0;
        }

        $hits = $terms->filter(fn (string $term): bool => str_contains(strtolower($haystack), $term))->count();

        return $hits / $terms->count();
    }
}
