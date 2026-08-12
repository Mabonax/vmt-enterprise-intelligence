<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Security\Enums;

enum GatewayCredentialStatus: string
{
    case Active = 'active';
    case Revoked = 'revoked';
    case Expired = 'expired';
}
