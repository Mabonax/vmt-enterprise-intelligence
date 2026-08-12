<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Connectors;

class EmailConnector extends RESTConnector
{
    public function key(): string
    {
        return 'email';
    }

    public function label(): string
    {
        return 'Email Connector';
    }
}
