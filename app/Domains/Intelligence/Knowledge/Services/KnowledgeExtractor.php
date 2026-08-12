<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Knowledge\Services;

class KnowledgeExtractor
{
    /**
     * @return list<array<string, mixed>>
     */
    public function extract(string $content): array
    {
        $entities = [];
        preg_match_all('/\b[A-Z][a-z]+(?:\s+[A-Z][a-z]+)*\b/', $content, $names);
        preg_match_all('/https?:\/\/\S+/i', $content, $urls);
        preg_match_all('/\b\d{4}-\d{2}-\d{2}\b/', $content, $dates);

        foreach (array_unique($names[0] ?? []) as $match) {
            $entities[] = ['type' => 'named_entity', 'name' => $match, 'confidence_score' => 0.72];
        }

        foreach (array_unique($urls[0] ?? []) as $match) {
            $entities[] = ['type' => 'url', 'name' => $match, 'confidence_score' => 0.95];
        }

        foreach (array_unique($dates[0] ?? []) as $match) {
            $entities[] = ['type' => 'date', 'name' => $match, 'confidence_score' => 0.88];
        }

        return $entities;
    }
}
