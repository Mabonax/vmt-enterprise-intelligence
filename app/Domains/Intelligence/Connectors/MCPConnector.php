<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Connectors;

class MCPConnector extends RESTConnector
{
    public function key(): string
    {
        return 'mcp';
    }

    public function label(): string
    {
        return 'MCP Connector';
    }
}
