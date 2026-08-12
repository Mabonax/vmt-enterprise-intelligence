<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Tools\Contracts;

use App\Domains\Intelligence\DTOs\ToolContext;
use App\Domains\Intelligence\Tools\Models\EnterpriseTool;

interface ConnectorInterface
{
    public function key(): string;

    public function label(): string;

    /**
     * @return array<string, mixed>
     */
    public function execute(EnterpriseTool $tool, array $payload, ToolContext $context): array;

    /**
     * @return array<string, mixed>
     */
    public function health(): array;
}
