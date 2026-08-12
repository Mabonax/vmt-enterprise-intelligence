<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Connectors;

class WebhookConnector extends RESTConnector
{
    public function key(): string
    {
        return 'webhook';
    }

    public function label(): string
    {
        return 'Webhook Connector';
    }
}
