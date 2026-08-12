<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\AdminConsole\Services;

use App\Domains\Intelligence\AdminConsole\Models\AdminConsoleAuditEvent;
use App\Domains\Intelligence\AdminConsole\Repositories\AdminConsoleAuditRepository;
use App\Models\User;
use Illuminate\Http\Request;

class AdminConsoleAuditService
{
    public function __construct(
        private readonly AdminConsoleAuditRepository $repository,
    ) {}

    public function record(
        ?User $actor,
        string $eventType,
        string $eventName,
        ?string $sourceContext = null,
        ?string $targetType = null,
        ?string $targetId = null,
        ?array $beforeState = null,
        ?array $afterState = null,
        array $metadata = [],
        ?Request $request = null,
    ): AdminConsoleAuditEvent {
        return AdminConsoleAuditEvent::query()->create([
            'uuid' => (string) str()->uuid(),
            'actor_id' => $actor?->id,
            'event_type' => $eventType,
            'event_name' => $eventName,
            'source_context' => $sourceContext,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'before_state' => $beforeState,
            'after_state' => $afterState,
            'metadata' => $metadata,
            'occurred_at' => now(),
        ]);
    }

    public function timeline(?string $search = null): array
    {
        return $this->repository->timeline($search)
            ->map(fn (AdminConsoleAuditEvent $event): array => [
                'id' => $event->id,
                'event_type' => $event->event_type,
                'event_name' => $event->event_name,
                'source_context' => $event->source_context,
                'target_type' => $event->target_type,
                'target_id' => $event->target_id,
                'occurred_at' => optional($event->occurred_at)->toIso8601String(),
                'metadata' => $event->metadata ?? [],
            ])
            ->all();
    }
}
