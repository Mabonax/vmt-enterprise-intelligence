<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Connectors;

class CalendarConnector extends RESTConnector
{
    public function key(): string
    {
        return 'calendar';
    }

    public function label(): string
    {
        return 'Calendar Connector';
    }
}
