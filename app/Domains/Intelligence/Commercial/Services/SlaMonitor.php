<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Services;

use App\Domains\Intelligence\Commercial\Models\SupportTicket;

class SlaMonitor
{
    public function summary(): array
    {
        return [
            'open_tickets' => SupportTicket::query()->whereNotIn('status', ['resolved', 'closed'])->count(),
            'resolved_tickets' => SupportTicket::query()->where('status', 'resolved')->count(),
            'breached_tickets' => SupportTicket::query()->where('severity', 'critical')->where('status', 'waiting_on_client')->count(),
        ];
    }
}