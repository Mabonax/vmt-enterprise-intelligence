<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Connectors;

class OpenAPIConnector extends RESTConnector
{
    public function key(): string
    {
        return 'openapi';
    }

    public function label(): string
    {
        return 'OpenAPI Connector';
    }
}
