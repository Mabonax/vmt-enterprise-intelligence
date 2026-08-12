<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\AdminConsole\Repositories;

use App\Domains\Intelligence\AdminConsole\Models\AdminConsoleAction;
use Illuminate\Database\Eloquent\Collection;

class AdminConsoleActionRepository
{
    public function latest(int $limit = 50): Collection
    {
        return AdminConsoleAction::query()->recent()->limit($limit)->get();
    }

    public function open(int $limit = 20): Collection
    {
        return AdminConsoleAction::query()->open()->recent()->limit($limit)->get();
    }
}
