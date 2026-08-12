<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Security\Services;

use Illuminate\Http\Request;

class GatewayRequestSignatureService
{
    public function requestHash(Request $request): string
    {
        return hash('sha256', implode('|', [
            strtoupper($request->method()),
            '/'.$request->path(),
            (string) $request->getContent(),
        ]));
    }

    public function expectedSignature(Request $request, string $secret, string $timestamp, string $nonce): string
    {
        return hash_hmac('sha256', implode('|', [
            $timestamp,
            $nonce,
            strtoupper($request->method()),
            '/'.$request->path(),
            hash('sha256', (string) $request->getContent()),
        ]), $secret);
    }
}
