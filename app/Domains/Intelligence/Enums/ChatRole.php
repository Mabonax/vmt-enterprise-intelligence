<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Enums;

enum ChatRole: string
{
    case System = 'system';
    case User = 'user';
    case Assistant = 'assistant';
    case Tool = 'tool';
}
