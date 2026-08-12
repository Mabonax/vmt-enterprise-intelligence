<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Security\Services;

class GatewayKeyHasher
{
    public function hash(string $value): string
    {
        return hash('sha256', $value);
    }

    public function matches(string $knownHash, string $candidate): bool
    {
        return hash_equals($knownHash, $this->hash($candidate));
    }
}
