<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\AdminConsole\Enums;

enum AdminConsoleAudienceType: string
{
    case Executive = 'executive';
    case Operator = 'operator';
    case Commercial = 'commercial';
    case Technical = 'technical';
    case Support = 'support';
    case Compliance = 'compliance';
}
