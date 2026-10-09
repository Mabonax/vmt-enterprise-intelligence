<?php

declare(strict_types=1);

namespace App\Http\Controllers\Gateway;

use App\Domains\Intelligence\Gateway\Models\GatewayRequest as GatewayRequestModel;
use App\Domains\Intelligence\Gateway\Services\ErpGatewayService;
use App\Domains\Intelligence\Gateway\Services\GatewayHealthService;
use App\Domains\Intelligence\Security\DTOs\AuthenticatedGatewayClientData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Gateway\GatewayCapabilityRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GatewayController extends Controller
{
    public function __construct(
        private readonly ErpGatewayService $gateway,
        private readonly GatewayHealthService $health,
    ) {}

    public function chat(GatewayCapabilityRequest $request): JsonResponse
    {
        return $this->handleCapability($request, 'chat');
    }

    public function summarise(GatewayCapabilityRequest $request): JsonResponse
    {
        return $this->handleCapability($request, 'summarise');
    }

    public function report(GatewayCapabilityRequest $request): JsonResponse
    {
        return $this->handleCapability($request, 'report');
    }

    public function translate(GatewayCapabilityRequest $request): JsonResponse
    {
        return $this->handleCapability($request, 'translate');
    }

    public function classify(GatewayCapabilityRequest $request): JsonResponse
    {
        return $this->handleCapability($request, 'classify');
    }

    public function search(GatewayCapabilityRequest $request): JsonResponse
    {
        return $this->handleCapability($request, 'search');
    }

    public function action(GatewayCapabilityRequest $request): JsonResponse
    {
        return $this->handleCapability($request, 'action');
    }

    public function show(Request $request, GatewayRequestModel $gatewayRequest): JsonResponse
    {
        /** @var AuthenticatedGatewayClientData|null $context */
        $context = $request->attributes->get('gateway.auth');

        // Request IDs must not allow cross-client or cross-organization access.
        abort_unless($context instanceof AuthenticatedGatewayClientData, 403);
        abort_unless(
            (string) $gatewayRequest->organization_id === $context->organizationId
            && (
                $context->clientId() !== null
                    ? (string) $gatewayRequest->gateway_client_id === (string) $context->clientId()
                    : ($context->legacyErp !== null
                        && (string) $gatewayRequest->connected_erp_id === (string) $context->legacyErp->getKey())
            ),
            404
        );

        return response()->json([
            'request' => [
                'id' => $gatewayRequest->getKey(),
                'status' => $gatewayRequest->status,
                'capability' => $gatewayRequest->capability,
                'provider' => $gatewayRequest->provider,
                'model' => $gatewayRequest->model,
                'verification' => $gatewayRequest->verification_payload,
                'completed_at' => optional($gatewayRequest->completed_at)->toIso8601String(),
            ],
        ]);
    }

    public function health(): JsonResponse
    {
        return response()->json($this->health->status());
    }

    private function handleCapability(GatewayCapabilityRequest $request, string $capability): JsonResponse
    {
        /** @var AuthenticatedGatewayClientData $context */
        $context = $request->attributes->get('gateway.auth');
        $result = $this->gateway->handle($context, $request->toData($capability), $request);

        return response()->json([
            'request_id' => $result['request']->getKey(),
            'trace_id' => $result['trace']->getKey(),
            'status' => $result['request']->status,
            'capability' => $result['request']->capability,
            'output' => $result['output'],
            'citations' => $result['citations'],
            'provider' => $result['provider'],
            'verification' => $result['verification'],
            'audit' => ['logged' => true],
            'tools' => $result['tools'],
            'correlation_id' => $request->attributes->get('gateway.correlation_id'),
        ]);
    }
}
