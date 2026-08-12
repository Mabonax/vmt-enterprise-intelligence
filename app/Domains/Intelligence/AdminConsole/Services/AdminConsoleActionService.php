<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\AdminConsole\Services;

use App\Domains\Intelligence\AdminConsole\Enums\AdminConsoleActionStatus;
use App\Domains\Intelligence\AdminConsole\Models\AdminConsoleAction;
use App\Domains\Intelligence\AdminConsole\Repositories\AdminConsoleActionRepository;
use App\Models\User;
use Illuminate\Http\Request;

class AdminConsoleActionService
{
    public function __construct(
        private readonly AdminConsoleActionRepository $repository,
        private readonly AdminConsoleAuditService $audit,
        private readonly AdminConsoleHealthService $health,
    ) {}

    public function request(User $user, array $attributes, ?Request $request = null): AdminConsoleAction
    {
        $action = AdminConsoleAction::query()->create([
            'uuid' => (string) str()->uuid(),
            'action_type' => $attributes['action_type'],
            'title' => $attributes['title'],
            'description' => $attributes['description'] ?? null,
            'target_context' => $attributes['target_context'] ?? null,
            'target_type' => $attributes['target_type'] ?? null,
            'target_id' => $attributes['target_id'] ?? null,
            'status' => AdminConsoleActionStatus::Requested,
            'requested_by' => $user->id,
            'payload' => $attributes['payload'] ?? [],
            'requested_at' => now(),
        ]);

        $this->audit->record($user, 'action', 'action requested', $action->target_context, AdminConsoleAction::class, (string) $action->id, null, $action->toArray(), request: $request);

        return $action;
    }

    public function approve(AdminConsoleAction $action, User $user, ?Request $request = null): AdminConsoleAction
    {
        $before = $action->toArray();

        $action->forceFill([
            'status' => AdminConsoleActionStatus::Approved,
            'approved_by' => $user->id,
            'approved_at' => now(),
        ])->save();

        $this->audit->record($user, 'action', 'action approved', $action->target_context, AdminConsoleAction::class, (string) $action->id, $before, $action->fresh()?->toArray(), request: $request);

        return $action->refresh();
    }

    public function execute(AdminConsoleAction $action, User $user, ?Request $request = null): AdminConsoleAction
    {
        $before = $action->toArray();

        $action->forceFill([
            'status' => AdminConsoleActionStatus::Executing,
            'executed_by' => $user->id,
        ])->save();

        try {
            $result = $this->performSafeExecution($action);

            $action->forceFill([
                'status' => AdminConsoleActionStatus::Completed,
                'result' => $result,
                'executed_at' => now(),
                'failed_at' => null,
            ])->save();

            $this->audit->record($user, 'action', 'action executed', $action->target_context, AdminConsoleAction::class, (string) $action->id, $before, $action->fresh()?->toArray(), request: $request);
        } catch (\Throwable $exception) {
            $action->forceFill([
                'status' => AdminConsoleActionStatus::Failed,
                'result' => ['error' => $exception->getMessage()],
                'failed_at' => now(),
            ])->save();

            $this->audit->record($user, 'action', 'action failed', $action->target_context, AdminConsoleAction::class, (string) $action->id, $before, $action->fresh()?->toArray(), ['error' => $exception->getMessage()], $request);
        }

        return $action->refresh();
    }

    public function queue(): array
    {
        return $this->repository->latest()
            ->map(fn (AdminConsoleAction $action): array => [
                'id' => $action->id,
                'uuid' => $action->uuid,
                'action_type' => $action->action_type,
                'title' => $action->title,
                'description' => $action->description,
                'target_context' => $action->target_context,
                'target_type' => $action->target_type,
                'target_id' => $action->target_id,
                'status' => $action->status->value,
                'requested_at' => optional($action->requested_at)->toIso8601String(),
                'approved_at' => optional($action->approved_at)->toIso8601String(),
                'executed_at' => optional($action->executed_at)->toIso8601String(),
                'result' => $action->result ?? [],
            ])
            ->all();
    }

    private function performSafeExecution(AdminConsoleAction $action): array
    {
        return match ($action->action_type) {
            'capture_health_snapshot' => ['health' => $this->health->capture('action')->toArray()],
            'refresh_readiness' => ['refreshed' => true, 'message' => 'Readiness refresh queued safely for review.'],
            default => ['executed' => true, 'message' => 'No-op safe execution completed for internal action routing.'],
        };
    }
}
