<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Contracts;

interface EmbeddingProvider
{
    public function embeddings(array $input): array;
}
