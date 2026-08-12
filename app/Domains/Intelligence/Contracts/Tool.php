<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Contracts;

interface Tool
{
    public function name(): string;

    public function description(): string;

    public function schema(): array;

    public function execute(array $payload): mixed;
}
