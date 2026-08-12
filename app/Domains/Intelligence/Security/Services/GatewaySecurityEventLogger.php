<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Security\Services;

use App\Domains\Intelligence\Security\DTOs\AuthenticatedGatewayClientData;
use App\Domains\Intelligence\Security\Models\GatewaySecurityEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GatewaySecurityEventLogger
{
    public function log(string $eventType, Request $request, ?AuthenticatedGatewayClientData $context = null, ?int $statusCode = null, ?string $failureReason = null, array $metadata = []): void
    {
        GatewaySecurityEvent::query()->create([
            'id' => (string) Str::uuid(),
            'gateway_tenant_id' => $context?->tenantId(),
            'gateway_client_id' => $context?->clientId(),
            'event_type' => $eventType,
            'severity' => $failureReason === null ? 'info' : 'warning',
            'auth_method' => $context?->authMethod->value,
            'endpoint' => $request->path(),
            'ip_address' => $request->ip(),
            'correlation_id' => (string) $request->attributes->get('gateway.correlation_id', $request->header('X-Correlation-ID', '')),
            'status_code' => $statusCode,
            'failure_reason' => $failureReason,
            'headers' => [
                'origin' => $request->header('Origin'),
                'user_agent' => $request->userAgent(),
            ],
            'metadata' => $metadata,
            'occurred_at' => now(),
        ]);
    }
}
