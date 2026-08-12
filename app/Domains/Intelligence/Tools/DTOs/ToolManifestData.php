<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Tools\DTOs;

final readonly class ToolManifestData
{
    /**
     * @param list<string> $permissions
     * @param list<string> $tags
     * @param list<array{name: string, constraint: string}> $dependencies
     * @param array<string, mixed> $inputs
     * @param array<string, mixed> $outputs
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public string $name,
        public string $slug,
        public string $description,
        public string $category,
        public string $version,
        public string $connector,
        public ?string $handlerClass = null,
        public ?string $author = null,
        public bool $enabled = true,
        public array $permissions = [],
        public array $tags = [],
        public array $dependencies = [],
        public array $inputs = [],
        public array $outputs = [],
        public array $metadata = [],
    ) {}
}
