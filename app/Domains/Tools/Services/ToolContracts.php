<?php

declare(strict_types=1);

namespace App\Domains\Tools\Services;

/**
 * Declares how ERP-discovered tools will be registered and resolved.
 */
interface ToolRegistryInterface
{
    public function register(array $toolDefinition): void;

    public function all(): array;
}

/**
 * Declares the callable contract for a discovered ERP tool.
 */
interface ErpToolInterface
{
    public function name(): string;

    public function invoke(array $payload): mixed;
}
