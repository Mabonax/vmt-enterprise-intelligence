<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Enums;

enum AgentStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Disabled = 'disabled';
    case Archived = 'archived';
}
