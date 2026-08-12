<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Contracts;

use App\Domains\Intelligence\DTOs\PromptContext;

interface PromptBuilder
{
    public function build(PromptContext $context): array;
}
