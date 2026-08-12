<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Knowledge\Services;

class KnowledgeScoringService
{
    /**
     * @param array<string, mixed> $signals
     */
    public function quality(array $signals): float
    {
        $scores = [
            (float) ($signals['freshness'] ?? 0.7),
            (float) ($signals['authority'] ?? 0.7),
            (float) ($signals['confidence'] ?? 0.7),
            (float) ($signals['completeness'] ?? 0.7),
            (float) ($signals['citation_quality'] ?? 0.7),
        ];

        return round(array_sum($scores) / count($scores), 2);
    }
}
