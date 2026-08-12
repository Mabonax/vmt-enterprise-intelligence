<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'gateway.client' => \App\Http\Middleware\AuthenticateGatewayClient::class,
            'gateway.correlation' => \App\Http\Middleware\Gateway\AssignGatewayCorrelationId::class,
            'gateway.resolve_tenant' => \App\Http\Middleware\Gateway\ResolveGatewayTenant::class,
            'gateway.replay' => \App\Http\Middleware\Gateway\EnforceGatewayReplayProtection::class,
            'gateway.context' => \App\Http\Middleware\Gateway\EnsureGatewayRequestContext::class,
        ]);

        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\App\Domains\Intelligence\Security\Exceptions\GatewayAuthenticationException $exception, \Illuminate\Http\Request $request) {
            if (! $request->is('api/gateway/*')) {
                return null;
            }

            app(\App\Domains\Intelligence\Security\Services\GatewaySecurityEventLogger::class)
                ->log('gateway.authentication.failed', $request, null, Response::HTTP_UNAUTHORIZED, $exception->getMessage());

            return response()->json([
                'message' => $exception->getMessage(),
                'correlation_id' => $request->attributes->get('gateway.correlation_id', $request->header('X-Correlation-ID')),
            ], Response::HTTP_UNAUTHORIZED);
        });

        $exceptions->render(function (\App\Domains\Intelligence\Security\Exceptions\GatewayAuthorizationException $exception, \Illuminate\Http\Request $request) {
            if (! $request->is('api/gateway/*')) {
                return null;
            }

            app(\App\Domains\Intelligence\Security\Services\GatewaySecurityEventLogger::class)
                ->log('gateway.authorization.failed', $request, $request->attributes->get('gateway.auth'), Response::HTTP_FORBIDDEN, $exception->getMessage());

            return response()->json([
                'message' => $exception->getMessage(),
                'correlation_id' => $request->attributes->get('gateway.correlation_id', $request->header('X-Correlation-ID')),
            ], Response::HTTP_FORBIDDEN);
        });

        $exceptions->render(function (\App\Domains\Intelligence\Security\Exceptions\GatewayReplayException $exception, \Illuminate\Http\Request $request) {
            if (! $request->is('api/gateway/*')) {
                return null;
            }

            app(\App\Domains\Intelligence\Security\Services\GatewaySecurityEventLogger::class)
                ->log('gateway.replay.blocked', $request, $request->attributes->get('gateway.auth'), Response::HTTP_CONFLICT, $exception->getMessage());

            return response()->json([
                'message' => $exception->getMessage(),
                'correlation_id' => $request->attributes->get('gateway.correlation_id', $request->header('X-Correlation-ID')),
            ], Response::HTTP_CONFLICT);
        });
    })->create();
