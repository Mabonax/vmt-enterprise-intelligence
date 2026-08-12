<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\AdminConsole\Repositories;

use App\Domains\Intelligence\AdminConsole\Models\AdminConsoleAuditEvent;
use Illuminate\Database\Eloquent\Collection;

class AdminConsoleAuditRepository
{
    public function timeline(?string $search = null, int $limit = 50): Collection
    {
        return AdminConsoleAuditEvent::query()
            ->when($search, function ($query, string $search): void {
                $query->where(function ($inner) use ($search): void {
                    $inner->where('event_type', 'like', "%{$search}%")
                        ->orWhere('event_name', 'like', "%{$search}%")
                        ->orWhere('source_context', 'like', "%{$search}%")
                        ->orWhere('target_type', 'like', "%{$search}%")
                        ->orWhere('target_id', 'like', "%{$search}%");
                });
            })
            ->recent()
            ->limit($limit)
            ->get();
    }
}
