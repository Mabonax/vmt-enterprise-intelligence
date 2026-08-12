<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Knowledge\Services;

use App\Domains\Intelligence\Knowledge\Models\KnowledgeDocument;
use App\Domains\Intelligence\Knowledge\Models\KnowledgeEmbedding;
use App\Domains\Intelligence\Knowledge\Models\KnowledgeLearningCycle;
use App\Domains\Intelligence\Knowledge\Models\KnowledgeMemory;
use App\Domains\Intelligence\Knowledge\Models\KnowledgeSearchLog;

class KnowledgeAnalyticsService
{
    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        return [
            'documents' => KnowledgeDocument::query()->count(),
            'memories' => KnowledgeMemory::query()->count(),
            'embeddings' => KnowledgeEmbedding::query()->count(),
            'searches' => KnowledgeSearchLog::query()->count(),
            'average_search_latency' => (int) round((float) KnowledgeSearchLog::query()->avg('latency_ms')),
            'learning_cycles' => KnowledgeLearningCycle::query()->count(),
        ];
    }
}
