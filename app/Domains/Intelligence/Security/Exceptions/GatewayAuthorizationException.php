<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Security\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;

class GatewayAuthorizationException extends AuthorizationException
{
}
