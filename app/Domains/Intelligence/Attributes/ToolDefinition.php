<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class ToolDefinition
{
    /**
     * @param list<string> $permissions
     * @param list<string> $tags
     * @param list<array<string, mixed>> $examples
     */
    public function __construct(
        public string $slug,
        public string $category,
        public ?string $description = null,
        public array $permissions = [],
        public array $tags = [],
        public string $version = '1.0.0',
        public string $provider = 'internal',
        public array $examples = [],
        public bool $deprecated = false,
        public bool $requiresApproval = false,
        public int $timeoutSeconds = 10,
    ) {}
}
