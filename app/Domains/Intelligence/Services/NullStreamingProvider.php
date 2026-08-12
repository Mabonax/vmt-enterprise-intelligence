<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Services;

use App\Domains\Intelligence\Contracts\StreamResponseContract;
use App\Domains\Intelligence\DTOs\StreamChunk;

class NullStreamingProvider implements StreamResponseContract
{
    public function __construct(
        private readonly array $payload = [],
    ) {}

    public function chunks(): iterable
    {
        yield new StreamChunk('progress', ['message' => 'Streaming disabled; returning buffered runtime events.']);
        yield new StreamChunk('complete', $this->payload);
    }
}
