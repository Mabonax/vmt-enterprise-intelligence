<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Security\Services;

use App\Domains\Intelligence\Security\Exceptions\GatewayReplayException;
use App\Domains\Intelligence\Security\Models\GatewayClient;
use App\Domains\Intelligence\Security\Models\GatewayNonce;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GatewayReplayProtectionService
{
    public function __construct(
        private readonly GatewayRequestSignatureService $signatures,
    ) {}

    public function ensureFresh(Request $request, GatewayClient $client): void
    {
        $timestamp = (string) $request->header('X-Gateway-Timestamp', '');
        $nonce = (string) $request->header('X-Gateway-Nonce', '');

        if ($timestamp === '' || $nonce === '') {
            return;
        }

        $instant = now()->setTimestamp((int) $timestamp);
        $window = (int) config('gateway.security.replay_window_seconds', 300);

        if (abs(now()->diffInSeconds($instant, false)) > $window) {
            throw new GatewayReplayException('The request timestamp is outside the allowed replay window.');
        }

        $duplicate = GatewayNonce::query()
            ->where('gateway_client_id', $client->getKey())
            ->where('nonce', $nonce)
            ->exists();

        if ($duplicate) {
            throw new GatewayReplayException('The request nonce has already been used.');
        }

        GatewayNonce::query()->create([
            'id' => (string) Str::uuid(),
            'gateway_client_id' => $client->getKey(),
            'nonce' => $nonce,
            'request_hash' => $this->signatures->requestHash($request),
            'expires_at' => now()->addSeconds($window),
        ]);
    }
}
