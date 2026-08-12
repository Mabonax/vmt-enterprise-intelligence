<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Services;

use App\Domains\Intelligence\Contracts\StreamingProvider;
use App\Domains\Intelligence\DTOs\ChatRequest;

class StreamingService
{
    public function __construct(
        private readonly StreamingProvider $provider,
    ) {}

    public function stream(ChatRequest $request): iterable
    {
        return $this->provider->stream($request);
    }
}
