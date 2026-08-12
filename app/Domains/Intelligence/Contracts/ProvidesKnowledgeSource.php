<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Contracts;

interface ProvidesKnowledgeSource
{
    public function knowledgeSourceReference(): array;
}
