<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\DTOs;

final readonly class ToolCall
{
    public function __construct(
        public string $name,
        public array $arguments = [],
        public ?string $result = null,
        public array $metadata = [],
    ) {}

    /**
     * @return array{name: string, arguments: array<string, mixed>, result: string|null, metadata: array<string, mixed>}
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'arguments' => $this->arguments,
            'result' => $this->result,
            'metadata' => $this->metadata,
        ];
    }
}
