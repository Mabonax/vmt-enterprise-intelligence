<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Contracts;

interface ProvidesIntelligenceTools
{
    public function intelligenceTools(): array;
}
