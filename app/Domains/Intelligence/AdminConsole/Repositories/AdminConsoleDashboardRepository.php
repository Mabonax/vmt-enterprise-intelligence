<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\AdminConsole\Repositories;

use App\Domains\Intelligence\AdminConsole\Models\AdminConsoleDashboard;

class AdminConsoleDashboardRepository
{
    public function forSlug(string $slug): ?AdminConsoleDashboard
    {
        return AdminConsoleDashboard::query()
            ->active()
            ->with(['widgets' => fn ($query) => $query->active()])
            ->where('slug', $slug)
            ->first();
    }
}
