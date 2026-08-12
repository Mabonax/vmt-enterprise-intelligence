<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Services;

use App\Domains\Intelligence\Commercial\Models\IntelligenceTenant;
use App\Domains\Intelligence\Commercial\Models\MaintenanceWindow;

class MaintenancePlanner
{
    public function schedule(IntelligenceTenant $tenant, string $title): MaintenanceWindow
    {
        return MaintenanceWindow::query()->create([
            'intelligence_tenant_id' => $tenant->id,
            'title' => $title,
            'starts_at' => now()->addWeek(),
            'ends_at' => now()->addWeek()->addHours(2),
            'status' => 'scheduled',
            'metadata' => [],
        ]);
    }
}