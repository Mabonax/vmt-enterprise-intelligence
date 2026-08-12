<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Services;

use App\Domains\Intelligence\Models\AiTool;
use App\Models\User;

class ToolApprovalService
{
    public function isApproved(AiTool $tool, ?User $user): bool
    {
        if (! $tool->requires_approval) {
            return true;
        }

        return $user?->hasRole('administrator') ?? false;
    }
}
