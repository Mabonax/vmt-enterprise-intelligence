<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Enums;

enum ExecutionStatus: string
{
    case Draft = 'draft';
    case Pending = 'pending';
    case Running = 'running';
    case Completed = 'completed';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
    case NeedsInput = 'needs_input';
}
