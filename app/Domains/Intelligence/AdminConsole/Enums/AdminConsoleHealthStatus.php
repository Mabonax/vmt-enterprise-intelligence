<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\AdminConsole\Enums;

enum AdminConsoleHealthStatus: string
{
    case Healthy = 'healthy';
    case Degraded = 'degraded';
    case AtRisk = 'at_risk';
    case Critical = 'critical';
    case Unknown = 'unknown';
}
