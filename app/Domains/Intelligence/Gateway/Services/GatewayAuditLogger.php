<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Gateway\Services;

use App\Domains\Intelligence\Gateway\Models\GatewayRequest;
use App\Domains\Intelligence\Security\DTOs\AuthenticatedGatewayClientData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GatewayAuditLogger
{
    public function record(AuthenticatedGatewayClientData $context, GatewayRequest $request, string $event, array $payload = []): void
    {
        DB::table('audit_entries')->insert([
            'id' => (string) Str::uuid(),
            'organization_id' => $context->organizationId,
            'user_id' => null,
            'event' => $event,
            'subject_type' => GatewayRequest::class,
            'subject_id' => $request->getKey(),
            'context' => json_encode($payload, JSON_UNESCAPED_SLASHES),
            'occurred_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function logConnection(AuthenticatedGatewayClientData $context, string $status, string $requestIdentifier, array $payload = [], ?int $responseCode = null): void
    {
        if ($context->legacyErp === null) {
            return;
        }

        DB::table('connection_logs')->insert([
            'id' => (string) Str::uuid(),
            'connected_erp_id' => $context->legacyErp->getKey(),
            'direction' => 'inbound',
            'status' => $status,
            'response_code' => $responseCode,
            'request_identifier' => $requestIdentifier,
            'context' => json_encode($payload, JSON_UNESCAPED_SLASHES),
            'logged_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
