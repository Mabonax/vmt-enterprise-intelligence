<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Security\Services;

use App\Domains\Intelligence\Security\DTOs\AuthenticatedGatewayClientData;
use App\Domains\Intelligence\Security\Exceptions\GatewayAuthorizationException;
use App\Domains\Intelligence\Security\Models\GatewayUsage;
use App\Domains\Intelligence\Security\Models\GatewayQuota;
use App\Domains\Intelligence\Security\Repositories\GatewayUsageRepository;
use App\Domains\Intelligence\Gateway\Models\GatewayRequest;
use Illuminate\Support\Str;

class GatewayUsageEnforcementService
{
    public function __construct(
        private readonly GatewayUsageRepository $usage,
    ) {}

    public function enforce(AuthenticatedGatewayClientData $context): void
    {
        $client = $context->client;

        if ($client === null) {
            return;
        }

        $now = now();
        $limits = [
            'minute' => [$client->rate_limit_per_minute, $now->copy()->subMinute()],
            'hour' => [$client->rate_limit_per_hour, $now->copy()->subHour()],
            'day' => [$client->rate_limit_per_day, $now->copy()->startOfDay()],
        ];

        foreach ($limits as [$limit, $since]) {
            if ($limit === null) {
                continue;
            }

            if ($this->usage->requestsSince($client->getKey(), $since) >= $limit) {
                throw new GatewayAuthorizationException('The gateway client has exceeded its rate limit.');
            }
        }

        if ($client->daily_quota !== null && $this->usage->tokensToday($client->getKey(), $now->copy()->startOfDay()) >= $client->daily_quota) {
            throw new GatewayAuthorizationException('The gateway client has exceeded its daily quota.');
        }
    }

    /**
     * @param array<string, mixed> $result
     */
    public function record(AuthenticatedGatewayClientData $context, GatewayRequest $request, array $result): void
    {
        GatewayUsage::query()->create([
            'id' => (string) Str::uuid(),
            'gateway_tenant_id' => $context->tenantId(),
            'gateway_client_id' => $context->clientId(),
            'gateway_request_id' => $request->getKey(),
            'correlation_id' => $request->correlation_id,
            'capability' => $request->capability,
            'provider' => $request->provider,
            'model' => $request->model,
            'request_count' => 1,
            'prompt_tokens' => (int) ($request->input_tokens ?? 0),
            'completion_tokens' => (int) ($request->output_tokens ?? 0),
            'total_tokens' => (int) (($request->input_tokens ?? 0) + ($request->output_tokens ?? 0)),
            'cost' => (float) ($request->cost ?? 0),
            'status_code' => 200,
            'measured_at' => now(),
            'metadata' => [
                'status' => $request->status,
                'verification_passed' => $result['verification']['passed'] ?? null,
            ],
        ]);

        if ($context->clientId() === null) {
            return;
        }

        GatewayQuota::query()
            ->where('gateway_client_id', $context->clientId())
            ->where('quota_type', 'daily_tokens')
            ->where('window_starts_at', '>=', now()->startOfDay())
            ->where('window_ends_at', '<=', now()->endOfDay())
            ->increment('consumed_value', (int) (($request->input_tokens ?? 0) + ($request->output_tokens ?? 0)));
    }
}
