<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Contracts;

interface Agent
{
    public function key(): string;

    public function systemPrompt(): string;

    public function availableTools(): array;

    public function memoryStrategy(): string;

    public function contextStrategy(): string;

    public function permissions(): array;
}
