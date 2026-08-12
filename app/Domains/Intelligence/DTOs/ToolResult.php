<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\DTOs;

final readonly class ToolResult
{
    public function __construct(
        public bool $success,
        public array $payload = [],
        public ?string $message = null,
        public array $metadata = [],
    ) {}
}
