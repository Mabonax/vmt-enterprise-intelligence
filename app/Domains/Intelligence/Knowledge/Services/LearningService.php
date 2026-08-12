<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Knowledge\Services;

use App\Domains\Intelligence\Knowledge\Models\KnowledgeLearningCycle;

class LearningService
{
    public function record(array $payload): KnowledgeLearningCycle
    {
        return KnowledgeLearningCycle::query()->create(array_merge([
            'workspace' => 'intelligence',
            'status' => 'captured',
            'confidence' => 0.75,
            'latency_ms' => 0,
        ], $payload));
    }
}
