<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\AdminConsole\Enums;

enum AdminConsoleActionStatus: string
{
    case Requested = 'requested';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Executing = 'executing';
    case Completed = 'completed';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
}
