<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Security\Enums;

enum GatewayClientStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Disabled = 'disabled';
    case Revoked = 'revoked';
}
