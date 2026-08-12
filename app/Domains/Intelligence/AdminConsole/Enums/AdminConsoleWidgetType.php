<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\AdminConsole\Enums;

enum AdminConsoleWidgetType: string
{
    case Metric = 'metric';
    case Chart = 'chart';
    case Table = 'table';
    case Timeline = 'timeline';
    case AlertList = 'alert_list';
    case ActionQueue = 'action_queue';
    case HealthPanel = 'health_panel';
    case ReadinessPanel = 'readiness_panel';
}
