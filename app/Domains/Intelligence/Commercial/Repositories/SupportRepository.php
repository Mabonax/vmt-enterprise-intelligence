<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Repositories;

use App\Domains\Intelligence\Commercial\Models\SupportTicket;
use Illuminate\Database\Eloquent\Collection;

class SupportRepository
{
    public function latest(int $limit = 25): Collection
    {
        return SupportTicket::query()->latest()->limit($limit)->get();
    }
}