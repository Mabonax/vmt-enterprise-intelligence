<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Tools\Sandbox;

use App\Domains\Intelligence\Tools\Exceptions\ToolSandboxException;
use Closure;
use Illuminate\Support\Facades\Cache;

class ToolExecutionSandbox
{
    /**
     * @param array<string, mixed> $limits
     * @return array<string, mixed>
     */
    public function run(string $toolSlug, Closure $callback, array $limits = []): array
    {
        $rateKey = 'tool-sandbox:'.$toolSlug;
        $maxExecutions = (int) ($limits['execution_limit'] ?? 100);
        $timeoutMs = (int) ($limits['timeout_ms'] ?? 10000);

        $count = (int) Cache::get($rateKey, 0);

        if ($count >= $maxExecutions) {
            throw new ToolSandboxException("Tool [{$toolSlug}] exceeded the configured execution limit.");
        }

        Cache::put($rateKey, $count + 1, now()->addMinute());

        $startedAt = microtime(true);
        $result = $callback();
        $durationMs = (int) ((microtime(true) - $startedAt) * 1000);

        if ($durationMs > $timeoutMs) {
            throw new ToolSandboxException("Tool [{$toolSlug}] exceeded the sandbox timeout.");
        }

        return [
            'result' => $result,
            'duration_ms' => $durationMs,
            'limits' => $limits,
        ];
    }
}
