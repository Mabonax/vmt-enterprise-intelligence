<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\DTOs;

final readonly class Citation
{
    public function __construct(
        public string $title,
        public ?string $url = null,
        public ?string $excerpt = null,
        public array $metadata = [],
    ) {}

    /**
     * @return array{title: string, url: string|null, excerpt: string|null, metadata: array<string, mixed>}
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'url' => $this->url,
            'excerpt' => $this->excerpt,
            'metadata' => $this->metadata,
        ];
    }
}
