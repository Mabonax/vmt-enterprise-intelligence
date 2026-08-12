<?php

declare(strict_types=1);

namespace App\Http\Middleware\Gateway;

use App\Domains\Intelligence\Security\Exceptions\GatewayReplayException;
use App\Domains\Intelligence\Security\Services\GatewayReplayProtectionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceGatewayReplayProtection
{
    public function __construct(
        private readonly GatewayReplayProtectionService $replay,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $context = $request->attributes->get('gateway.auth');

        if ($context?->client !== null) {
            $this->replay->ensureFresh($request, $context->client);
        }

        return $next($request);
    }
}
