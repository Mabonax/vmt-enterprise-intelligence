<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Contracts;

use App\Domains\Intelligence\DTOs\ChatRequest;

interface StreamingProvider
{
    public function stream(ChatRequest $request): iterable;
}
