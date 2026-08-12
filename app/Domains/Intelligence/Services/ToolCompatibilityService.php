<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Services;

use App\Domains\Intelligence\Tools\Models\EnterpriseTool;

class ToolCompatibilityService
{
    public function isCompatible(EnterpriseTool $tool, array $context = []): bool
    {
        return ($tool->status ?? 'active') === 'active';
    }
}
