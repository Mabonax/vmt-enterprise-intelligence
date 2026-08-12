<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Security\Contracts;

use App\Domains\Intelligence\Security\DTOs\AuthenticatedGatewayClientData;
use Illuminate\Http\Request;

interface GatewayAuthenticator
{
    public function authenticate(Request $request): AuthenticatedGatewayClientData;
}
