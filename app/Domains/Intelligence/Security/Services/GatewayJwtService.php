<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Security\Services;

use App\Domains\Intelligence\Security\Exceptions\GatewayAuthenticationException;

class GatewayJwtService
{
    /**
     * @return array<string, mixed>
     */
    public function decode(string $jwt, string $secret): array
    {
        $segments = explode('.', $jwt);

        if (count($segments) !== 3) {
            throw new GatewayAuthenticationException('The JWT structure is invalid.');
        }

        [$encodedHeader, $encodedPayload, $signature] = $segments;

        $expectedSignature = $this->base64UrlEncode(hash_hmac('sha256', $encodedHeader.'.'.$encodedPayload, $secret, true));

        if (! hash_equals($expectedSignature, $signature)) {
            throw new GatewayAuthenticationException('The JWT signature is invalid.');
        }

        $payload = json_decode((string) base64_decode(strtr($encodedPayload, '-_', '+/')), true);

        if (! is_array($payload)) {
            throw new GatewayAuthenticationException('The JWT payload is invalid.');
        }

        if (isset($payload['exp']) && now()->timestamp >= (int) $payload['exp']) {
            throw new GatewayAuthenticationException('The JWT has expired.');
        }

        return $payload;
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
