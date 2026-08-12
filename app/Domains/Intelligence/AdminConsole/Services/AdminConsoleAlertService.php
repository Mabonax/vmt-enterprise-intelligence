<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\AdminConsole\Services;

use App\Domains\Intelligence\AdminConsole\Enums\AdminConsoleAlertSeverity;
use App\Domains\Intelligence\AdminConsole\Enums\AdminConsoleAlertStatus;
use App\Domains\Intelligence\AdminConsole\Models\AdminConsoleAlert;
use App\Domains\Intelligence\AdminConsole\Repositories\AdminConsoleAlertRepository;
use App\Models\User;
use Illuminate\Http\Request;

class AdminConsoleAlertService
{
    public function __construct(
        private readonly AdminConsoleAlertRepository $repository,
        private readonly AdminConsoleAuditService $audit,
    ) {}

    public function create(array $attributes): AdminConsoleAlert
    {
        $duplicate = $this->repository->findDuplicate(
            $attributes['alert_type'],
            $attributes['source_context'] ?? null,
            $attributes['source_type'] ?? null,
            $attributes['source_id'] ?? null,
        );

        if ($duplicate !== null) {
            return $duplicate;
        }

        return AdminConsoleAlert::query()->create([
            'uuid' => (string) str()->uuid(),
            'alert_type' => $attributes['alert_type'],
            'severity' => ($attributes['severity'] instanceof AdminConsoleAlertSeverity ? $attributes['severity'] : AdminConsoleAlertSeverity::from($attributes['severity'] ?? AdminConsoleAlertSeverity::Info->value))->value,
            'title' => $attributes['title'],
            'message' => $attributes['message'],
            'source_context' => $attributes['source_context'] ?? null,
            'source_type' => $attributes['source_type'] ?? null,
            'source_id' => $attributes['source_id'] ?? null,
            'status' => AdminConsoleAlertStatus::Open->value,
            'assigned_to' => $attributes['assigned_to'] ?? null,
            'first_seen_at' => $attributes['first_seen_at'] ?? now(),
            'metadata' => $attributes['metadata'] ?? [],
        ]);
    }

    public function acknowledge(AdminConsoleAlert $alert, User $user, ?Request $request = null): AdminConsoleAlert
    {
        $before = $alert->toArray();

        $alert->forceFill([
            'status' => AdminConsoleAlertStatus::Acknowledged,
            'acknowledged_at' => now(),
            'assigned_to' => $user->id,
        ])->save();

        $this->audit->record($user, 'alert', 'alert acknowledged', $alert->source_context, AdminConsoleAlert::class, (string) $alert->id, $before, $alert->fresh()?->toArray(), request: $request);

        return $alert->refresh();
    }

    public function resolve(AdminConsoleAlert $alert, User $user, ?Request $request = null): AdminConsoleAlert
    {
        $before = $alert->toArray();

        $alert->forceFill([
            'status' => AdminConsoleAlertStatus::Resolved,
            'resolved_at' => now(),
            'assigned_to' => $user->id,
        ])->save();

        $this->audit->record($user, 'alert', 'alert resolved', $alert->source_context, AdminConsoleAlert::class, (string) $alert->id, $before, $alert->fresh()?->toArray(), request: $request);

        return $alert->refresh();
    }

    public function dismiss(AdminConsoleAlert $alert, User $user, ?Request $request = null): AdminConsoleAlert
    {
        $before = $alert->toArray();

        $alert->forceFill([
            'status' => AdminConsoleAlertStatus::Dismissed,
            'resolved_at' => now(),
            'assigned_to' => $user->id,
        ])->save();

        $this->audit->record($user, 'alert', 'alert dismissed', $alert->source_context, AdminConsoleAlert::class, (string) $alert->id, $before, $alert->fresh()?->toArray(), request: $request);

        return $alert->refresh();
    }

    public function feed(): array
    {
        return $this->repository->latest()
            ->map(fn (AdminConsoleAlert $alert): array => [
                'id' => $alert->id,
                'uuid' => $alert->uuid,
                'alert_type' => $alert->alert_type,
                'severity' => $alert->severity->value,
                'title' => $alert->title,
                'message' => $alert->message,
                'status' => $alert->status->value,
                'source_context' => $alert->source_context,
                'source_type' => $alert->source_type,
                'source_id' => $alert->source_id,
                'first_seen_at' => optional($alert->first_seen_at)->toIso8601String(),
                'acknowledged_at' => optional($alert->acknowledged_at)->toIso8601String(),
                'resolved_at' => optional($alert->resolved_at)->toIso8601String(),
                'metadata' => $alert->metadata ?? [],
            ])
            ->all();
    }
}
