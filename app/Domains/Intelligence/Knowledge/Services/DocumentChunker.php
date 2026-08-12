<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Knowledge\Services;

class DocumentChunker
{
    /**
     * @return list<array<string, mixed>>
     */
    public function chunk(string $content, string $strategy = 'paragraph', int $size = 500, int $overlap = 50): array
    {
        return match ($strategy) {
            'token' => $this->chunkByTokens($content, $size, $overlap),
            'semantic' => $this->chunkByParagraph($content, true),
            default => $this->chunkByParagraph($content, false),
        };
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function chunkByParagraph(string $content, bool $semantic): array
    {
        $parts = preg_split('/\R{2,}/', trim($content)) ?: [];

        return collect($parts)
            ->filter(fn (string $part): bool => trim($part) !== '')
            ->values()
            ->map(fn (string $part, int $index): array => [
                'content' => trim($part),
                'strategy' => $semantic ? 'semantic' : 'paragraph',
                'order' => $index + 1,
                'token_count' => str_word_count($part),
                'overlap' => 0,
                'checksum' => sha1($part),
            ])->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function chunkByTokens(string $content, int $size, int $overlap): array
    {
        $words = preg_split('/\s+/', trim($content)) ?: [];
        $chunks = [];
        $step = max(1, $size - $overlap);

        for ($i = 0; $i < count($words); $i += $step) {
            $slice = array_slice($words, $i, $size);

            if ($slice === []) {
                continue;
            }

            $text = implode(' ', $slice);
            $chunks[] = [
                'content' => $text,
                'strategy' => 'token',
                'order' => count($chunks) + 1,
                'token_count' => count($slice),
                'overlap' => $overlap,
                'checksum' => sha1($text),
            ];
        }

        return $chunks;
    }
}
