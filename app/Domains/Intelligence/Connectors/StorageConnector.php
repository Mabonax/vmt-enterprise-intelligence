<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Connectors;

class StorageConnector extends FilesystemConnector
{
    public function key(): string
    {
        return 'storage';
    }

    public function label(): string
    {
        return 'Storage Connector';
    }
}
