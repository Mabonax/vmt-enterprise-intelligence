<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\AdminConsole\Repositories;

use App\Domains\Intelligence\AdminConsole\Enums\AdminConsoleAlertStatus;
use App\Domains\Intelligence\AdminConsole\Models\AdminConsoleAlert;
use Illuminate\Database\Eloquent\Collection;

class AdminConsoleAlertRepository
{
    public function latest(int $limit = 50): Collection
    {
        return AdminConsoleAlert::query()->recent()->limit($limit)->get();
    }

    public function unresolved(int $limit = 20): Collection
    {
        return AdminConsoleAlert::query()->unresolved()->recent()->limit($limit)->get();
    }

    public function findDuplicate(string $alertType, ?string $sourceContext, ?string $sourceType, ?string $sourceId): ?AdminConsoleAlert
    {
        return AdminConsoleAlert::query()
            ->where('alert_type', $alertType)
            ->where('source_context', $sourceContext)
            ->where('source_type', $sourceType)
            ->where('source_id', $sourceId)
            ->whereIn('status', [
                AdminConsoleAlertStatus::Open->value,
                AdminConsoleAlertStatus::Acknowledged->value,
                AdminConsoleAlertStatus::Investigating->value,
            ])
            ->latest('id')
            ->first();
    }
}
