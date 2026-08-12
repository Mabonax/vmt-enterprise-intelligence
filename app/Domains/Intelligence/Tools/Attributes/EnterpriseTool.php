<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Tools\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class EnterpriseTool
{
    /**
     * @param list<string> $permissions
     * @param list<string> $tags
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public string $name,
        public string $category,
        public string $description,
        public string $version = '1.0.0',
        public string $connector = 'laravel',
        public array $permissions = [],
        public array $tags = [],
        public bool $enabled = true,
        public array $metadata = [],
    ) {}
}
