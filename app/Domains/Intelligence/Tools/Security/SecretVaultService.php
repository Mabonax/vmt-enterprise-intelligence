<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Tools\Security;

use App\Domains\Intelligence\Tools\Models\ToolCredential;
use Illuminate\Contracts\Encryption\Encrypter;

class SecretVaultService
{
    public function __construct(
        private readonly Encrypter $encrypter,
    ) {}

    /**
     * @param array<string, mixed> $payload
     */
    public function store(string $name, string $slug, string $type, array $payload, array $metadata = []): ToolCredential
    {
        return ToolCredential::query()->updateOrCreate(
            ['slug' => $slug],
            [
                'name' => $name,
                'type' => $type,
                'status' => 'active',
                'encrypted_payload' => $this->encrypter->encrypt(json_encode($payload, JSON_THROW_ON_ERROR)),
                'last_rotated_at' => now(),
                'metadata' => $metadata,
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function reveal(ToolCredential $credential): array
    {
        /** @var string $decrypted */
        $decrypted = $this->encrypter->decrypt($credential->encrypted_payload);

        return json_decode($decrypted, true, 512, JSON_THROW_ON_ERROR);
    }
}
