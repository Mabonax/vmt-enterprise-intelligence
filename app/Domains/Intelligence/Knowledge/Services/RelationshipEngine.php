<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Knowledge\Services;

class RelationshipEngine
{
    /**
     * @param list<array<string, mixed>> $entities
     * @return list<array<string, mixed>>
     */
    public function discover(array $entities): array
    {
        $relationships = [];
        $first = collect($entities)->values();

        for ($i = 0; $i < $first->count() - 1; $i++) {
            $source = $first[$i];
            $target = $first[$i + 1];

            $relationships[] = [
                'source_name' => $source['name'],
                'target_name' => $target['name'],
                'relationship' => 'related_to',
                'confidence_score' => round(min($source['confidence_score'], $target['confidence_score']), 2),
            ];
        }

        return $relationships;
    }
}
