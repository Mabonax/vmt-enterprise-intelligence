<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCommercialConsoleEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(config('deployment.commercial_console_enabled', false), 404);

        return $next($request);
    }
}
