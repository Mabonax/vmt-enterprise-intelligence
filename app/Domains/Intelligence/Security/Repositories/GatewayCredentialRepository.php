<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Security\Repositories;

use App\Domains\Intelligence\Security\Enums\GatewayCredentialStatus;
use App\Domains\Intelligence\Security\Models\GatewayApiCredential;

class GatewayCredentialRepository
{
    public function findActiveByIdentifier(string $identifier): ?GatewayApiCredential
    {
        return GatewayApiCredential::query()
            ->with('client.tenant')
            ->where('key_identifier', $identifier)
            ->where('status', GatewayCredentialStatus::Active->value)
            ->first();
    }
}
