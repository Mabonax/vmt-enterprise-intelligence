<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Gateway\Services;

use App\Domains\Intelligence\Gateway\DTOs\GatewayCapabilityRequestData;
use App\Domains\Intelligence\Security\DTOs\AuthenticatedGatewayClientData;
use App\Domains\Intelligence\Security\Services\GatewayAuthorizationService;

class GatewayPermissionContextService
{
    public function __construct(
        private readonly GatewayAuthorizationService $authorization,
    ) {}

    public function authorize(AuthenticatedGatewayClientData $context, GatewayCapabilityRequestData $request): void
    {
        $this->authorization->authorize($context, $request);
    }

    public function contextPayload(AuthenticatedGatewayClientData $context, GatewayCapabilityRequestData $request): array
    {
        return [
            'organization_id' => $request->organizationId,
            'tenant_id' => $context->tenantId(),
            'client' => [
                'id' => $context->clientId(),
                'name' => $context->clientName,
                'client_key' => $context->clientKey,
                'auth_method' => $context->authMethod->value,
            ],
            'actor' => $request->actor->toArray(),
            'subject' => $request->subject?->toArray(),
            'capability' => $request->capability,
            'context_payload' => $request->context,
            'knowledge_references' => $request->knowledgeReferences,
            'requested_actions' => $request->actions,
        ];
    }
}
