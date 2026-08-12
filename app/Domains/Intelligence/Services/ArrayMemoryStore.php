<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Services;

use App\Domains\Intelligence\Contracts\MemoryStore;

class ArrayMemoryStore implements MemoryStore
{
    /**
     * @var array<string, mixed>
     */
    private array $store = [];

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->store[$key] ?? $default;
    }

    public function put(string $key, mixed $value, ?int $ttlSeconds = null): void
    {
        unset($ttlSeconds);

        $this->store[$key] = $value;
    }

    public function forget(string $key): void
    {
        unset($this->store[$key]);
    }
}
