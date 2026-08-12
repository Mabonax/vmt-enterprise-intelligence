<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Contracts;

interface ProvidesIntelligenceContext
{
    public function intelligenceContext(): array;
}
