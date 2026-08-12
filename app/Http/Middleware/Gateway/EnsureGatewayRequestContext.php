<?php

declare(strict_types=1);

namespace App\Http\Middleware\Gateway;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureGatewayRequestContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->attributes->set('gateway.request_context', [
            'correlation_id' => $request->attributes->get('gateway.correlation_id'),
            'ip' => $request->ip(),
            'origin' => $request->header('Origin'),
        ]);

        return $next($request);
    }
}
