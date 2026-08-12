<?php

declare(strict_types=1);

namespace App\Http\Middleware\Gateway;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AssignGatewayCorrelationId
{
    public function handle(Request $request, Closure $next): Response
    {
        $correlationId = (string) ($request->header('X-Correlation-ID')
            ?: $request->input('correlation_id')
            ?: Str::uuid()->toString());

        $request->attributes->set('gateway.correlation_id', $correlationId);

        $response = $next($request);
        $response->headers->set('X-Correlation-ID', $correlationId);

        return $response;
    }
}
