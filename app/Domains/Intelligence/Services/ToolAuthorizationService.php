<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Services;

use App\Domains\Intelligence\DTOs\ToolContext;
use App\Domains\Intelligence\Tools\Models\EnterpriseTool;

class ToolAuthorizationService
{
    /**
     * @return array<string, mixed>
     */
    public function authorize(EnterpriseTool $tool, ToolContext $context): array
    {
        $user = $context->user;
        $permissions = $tool->permissions ?? [];
        $hasPermission = $permissions === []
            || $user === null
            || collect($permissions)->contains(fn (string $permission): bool => $user->can($permission))
            || ($user?->hasRole('administrator') ?? false);
        $tenantOk = $context->conversation === null
            || ! isset($context->conversation->metadata['organization_id'])
            || $context->conversation->metadata['organization_id'] === $user?->organization_id;

        return [
            'approved' => $hasPermission && $tenantOk,
            'user_id' => $user?->id,
            'tenant' => $user?->organization_id,
            'department' => $context->metadata['department'] ?? null,
            'workspace' => $context->metadata['workspace'] ?? 'intelligence',
            'project' => $context->metadata['project'] ?? null,
            'security_level' => $context->metadata['security_level'] ?? 'standard',
            'status' => $hasPermission && $tenantOk ? 'authorized' : 'denied',
        ];
    }
}
