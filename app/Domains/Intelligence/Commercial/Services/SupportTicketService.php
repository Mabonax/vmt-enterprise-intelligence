<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Services;

use App\Domains\Intelligence\Commercial\Models\IntelligenceTenant;
use App\Domains\Intelligence\Commercial\Models\SupportPlan;
use App\Domains\Intelligence\Commercial\Models\SupportTicket;

class SupportTicketService
{
    public function open(IntelligenceTenant $tenant, string $title, string $severity = 'medium'): SupportTicket
    {
        $plan = SupportPlan::query()->firstOrCreate(
            ['intelligence_tenant_id' => $tenant->id],
            [
                'intelligence_package_id' => $tenant->subscriptions()->latest()->first()?->intelligence_package_id,
                'support_level' => 'standard',
                'sla_name' => 'Business Hours',
                'coverage_hours' => '08:00-17:00',
                'metadata' => [],
            ],
        );

        return SupportTicket::query()->create([
            'intelligence_tenant_id' => $tenant->id,
            'support_plan_id' => $plan->id,
            'title' => $title,
            'status' => 'new',
            'severity' => $severity,
            'opened_at' => now(),
            'ticket_payload' => [],
            'metadata' => [],
        ]);
    }

    public function advance(SupportTicket $ticket, string $status): SupportTicket
    {
        $ticket->forceFill([
            'status' => $status,
            'resolved_at' => in_array($status, ['resolved', 'closed'], true) ? now() : $ticket->resolved_at,
        ])->save();

        return $ticket->fresh();
    }
}