<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Security\Services;

use App\Domains\Intelligence\Gateway\DTOs\GatewayCapabilityRequestData;
use App\Domains\Intelligence\Security\DTOs\AuthenticatedGatewayClientData;
use App\Domains\Intelligence\Security\Exceptions\GatewayAuthorizationException;
use App\Domains\Intelligence\Security\Models\GatewayPolicy;

class GatewayAuthorizationService
{
    public function authorize(AuthenticatedGatewayClientData $context, GatewayCapabilityRequestData $request): void
    {
        if ($context->organizationId !== $request->organizationId) {
            throw new GatewayAuthorizationException('The gateway client is outside the requested tenant or organization scope.');
        }

        $erpSystem = $context->client?->metadata['erp_system'] ?? $context->legacyErp?->system_key;
        if (is_string($erpSystem) && $erpSystem !== '' && $erpSystem !== $request->erpSystem) {
            throw new GatewayAuthorizationException('The gateway client is not authorized for the requested ERP system.');
        }

        $requiredScope = $request->capability;

        if ($context->scopes !== [] && ! in_array($requiredScope, $context->scopes, true)) {
            throw new GatewayAuthorizationException("The gateway client does not own the [{$requiredScope}] scope.");
        }

        if ($context->capabilities !== [] && ! in_array($request->capability, $context->capabilities, true)) {
            throw new GatewayAuthorizationException("The gateway client cannot call the [{$request->capability}] capability.");
        }
    }

    public function assertProviderAllowed(AuthenticatedGatewayClientData $context, string $provider, string $capability, ?string $model = null): void
    {
        if ($context->providers !== [] && ! in_array($provider, $context->providers, true)) {
            throw new GatewayAuthorizationException("The provider [{$provider}] is not enabled for this gateway client.");
        }

        if ($model !== null && $context->models !== [] && ! in_array($model, $context->models, true)) {
            throw new GatewayAuthorizationException("The model [{$model}] is not enabled for this gateway client.");
        }

        if ($context->tenant === null) {
            return;
        }

        $policies = GatewayPolicy::query()
            ->where('gateway_tenant_id', $context->tenant->getKey())
            ->where('status', 'active')
            ->get();

        foreach ($policies as $policy) {
            $providerRules = $policy->provider_rules ?? [];
            $capabilityRules = $policy->capability_rules ?? [];
            $modelRules = $policy->model_rules ?? [];

            if (($capabilityRules[$capability] ?? null) !== null) {
                $allowedProviders = array_values((array) $capabilityRules[$capability]);

                if ($allowedProviders !== [] && ! in_array($provider, $allowedProviders, true)) {
                    throw new GatewayAuthorizationException("Tenant policy [{$policy->name}] disallows provider [{$provider}] for capability [{$capability}].");
                }
            }

            if (($providerRules['allow'] ?? null) !== null && ! in_array($provider, array_values((array) $providerRules['allow']), true)) {
                throw new GatewayAuthorizationException("Tenant policy [{$policy->name}] disallows provider [{$provider}].");
            }

            if ($model !== null && ($modelRules['allow'] ?? null) !== null && ! in_array($model, array_values((array) $modelRules['allow']), true)) {
                throw new GatewayAuthorizationException("Tenant policy [{$policy->name}] disallows model [{$model}].");
            }
        }
    }
}
