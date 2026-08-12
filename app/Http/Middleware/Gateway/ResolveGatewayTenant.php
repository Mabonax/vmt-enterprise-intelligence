<?php

declare(strict_types=1);

namespace App\Http\Middleware\Gateway;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveGatewayTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $context = $request->attributes->get('gateway.auth');

        if ($context !== null) {
            $request->attributes->set('gateway.tenant', $context->tenant);
            $request->attributes->set('gateway.client', $context->client);
        }

        return $next($request);
    }
}
