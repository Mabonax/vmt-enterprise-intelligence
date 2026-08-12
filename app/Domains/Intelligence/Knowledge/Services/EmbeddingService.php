<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Knowledge\Services;

class EmbeddingService
{
    /**
     * @return array<string, mixed>
     */
    public function generate(string $content, ?string $provider = null, ?string $model = null): array
    {
        $provider = $provider ?? (string) config('intelligence.knowledge.embeddings.provider', 'internal');
        $model = $model ?? (string) config('intelligence.knowledge.embeddings.model', 'hash-vector-v1');
        $hash = hash('sha256', $content);
        $vector = [];

        foreach (str_split(substr($hash, 0, 32), 2) as $pair) {
            $vector[] = round(hexdec($pair) / 255, 6);
        }

        return [
            'provider' => $provider,
            'model' => $model,
            'vector' => $vector,
            'dimensions' => count($vector),
            'checksum' => sha1($content),
        ];
    }

    public function cosineSimilarity(array $a, array $b): float
    {
        $dot = 0.0;
        $magnitudeA = 0.0;
        $magnitudeB = 0.0;
        $length = min(count($a), count($b));

        for ($i = 0; $i < $length; $i++) {
            $dot += $a[$i] * $b[$i];
            $magnitudeA += $a[$i] ** 2;
            $magnitudeB += $b[$i] ** 2;
        }

        if ($magnitudeA === 0.0 || $magnitudeB === 0.0) {
            return 0.0;
        }

        return $dot / (sqrt($magnitudeA) * sqrt($magnitudeB));
    }
}
