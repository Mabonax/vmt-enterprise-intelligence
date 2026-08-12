<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Contracts;

use App\Domains\Intelligence\DTOs\StreamChunk;

interface StreamResponseContract
{
    /**
     * @return iterable<StreamChunk>
     */
    public function chunks(): iterable;
}
