<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Services;

use App\Domains\Intelligence\Models\SemanticMemory;
use App\Models\User;

class MemoryReviewService
{
    public function approve(SemanticMemory $memory, User $user): SemanticMemory
    {
        $memory->forceFill([
            'approved_by' => $user->id,
            'reviewed_at' => now(),
        ])->save();

        return $memory->refresh();
    }
}
