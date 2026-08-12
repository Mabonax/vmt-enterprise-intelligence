<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Domains\Intelligence\Security\Services\GatewayAuthenticationService;
use App\Domains\Intelligence\Security\Services\GatewaySecurityEventLogger;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateGatewayClient
{
    public function __construct(
        private readonly GatewayAuthenticationService $authenticator,
        private readonly GatewaySecurityEventLogger $events,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $context = $this->authenticator->authenticate($request);

        $request->attributes->set('gateway.auth', $context);
        $request->attributes->set('gateway.erp', $context->legacyErp);
        $request->attributes->set('gateway.client', $context->client);
        $request->attributes->set('gateway.tenant', $context->tenant);
        $request->attributes->set('gateway.auth_method', $context->authMethod->value);

        $this->events->log('gateway.authentication.succeeded', $request, $context, 200);

        return $next($request);
    }
}
