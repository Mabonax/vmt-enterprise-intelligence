<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Connectors;

class CacheConnector extends RedisConnector
{
    public function key(): string
    {
        return 'cache';
    }

    public function label(): string
    {
        return 'Cache Connector';
    }
}
