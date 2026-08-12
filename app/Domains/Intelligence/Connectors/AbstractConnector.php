<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Connectors;

use App\Domains\Intelligence\Tools\Contracts\ConnectorInterface;

abstract class AbstractConnector implements ConnectorInterface
{
    public function health(): array
    {
        return [
            'status' => 'healthy',
            'checked_at' => now()->toIso8601String(),
        ];
    }
}
