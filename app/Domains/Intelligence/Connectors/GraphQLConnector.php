<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Connectors;

class GraphQLConnector extends RESTConnector
{
    public function key(): string
    {
        return 'graphql';
    }

    public function label(): string
    {
        return 'GraphQL Connector';
    }
}
