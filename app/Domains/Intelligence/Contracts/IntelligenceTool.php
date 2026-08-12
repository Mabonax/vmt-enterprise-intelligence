<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Contracts;

interface IntelligenceTool extends Tool
{
    public function slug(): string;

    public function category(): string;

    public function permissions(): array;

    public function responseSchema(): array;
}
