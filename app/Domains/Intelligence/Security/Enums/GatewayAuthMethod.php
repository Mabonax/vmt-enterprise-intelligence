<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Security\Enums;

enum GatewayAuthMethod: string
{
    case LegacySharedSecret = 'legacy_shared_secret';
    case ApiKey = 'api_key';
    case Hmac = 'hmac';
    case Jwt = 'jwt';
}
