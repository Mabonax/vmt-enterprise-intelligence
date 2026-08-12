<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Enums;

enum SupportTicketStatus: string
{
    case New = 'new';
    case Triaged = 'triaged';
    case Assigned = 'assigned';
    case InProgress = 'in_progress';
    case WaitingOnClient = 'waiting_on_client';
    case Resolved = 'resolved';
    case Closed = 'closed';
}