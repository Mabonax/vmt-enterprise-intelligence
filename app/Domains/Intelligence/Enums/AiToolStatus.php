<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Enums;

enum AiToolStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Disabled = 'disabled';
    case Deprecated = 'deprecated';
}
