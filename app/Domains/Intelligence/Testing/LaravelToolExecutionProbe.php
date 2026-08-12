<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Testing;

use App\Domains\Intelligence\DTOs\ToolContext;

class LaravelToolExecutionProbe
{
    /**
     * @return array<string, mixed>
     */
    public function inspect(array $payload, ToolContext $context): array
    {
        return [
            'user_id' => $context->user?->id,
            'echo' => $payload,
            'status' => 'ok',
        ];
    }
}
