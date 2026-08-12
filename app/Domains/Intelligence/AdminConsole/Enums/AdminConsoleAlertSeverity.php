<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\AdminConsole\Enums;

enum AdminConsoleAlertSeverity: string
{
    case Info = 'info';
    case Warning = 'warning';
    case Critical = 'critical';
    case Blocker = 'blocker';
}
