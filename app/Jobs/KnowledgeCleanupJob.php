<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Domains\Intelligence\Knowledge\Models\KnowledgeMemory;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class KnowledgeCleanupJob implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        KnowledgeMemory::query()
            ->whereNotNull('expiry_at')
            ->where('expiry_at', '<', now())
            ->delete();
    }
}
