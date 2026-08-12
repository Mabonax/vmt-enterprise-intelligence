<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Operations\Services;

use App\Domains\Intelligence\Operations\Models\EnterpriseEvent;
use App\Domains\Intelligence\Operations\Models\EnterpriseMission;
use Illuminate\Support\Str;

class EnterpriseEventBus
{
    public function publish(string $eventType, ?EnterpriseMission $mission = null, array $payload = [], ?string $subjectType = null, ?string $subjectId = null): EnterpriseEvent
    {
        return EnterpriseEvent::query()->create([
            'enterprise_mission_id' => $mission?->id,
            'event_type' => $eventType,
            'event_key' => (string) Str::uuid(),
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'payload' => $payload,
            'metadata' => [
                'replayable' => true,
            ],
        ]);
    }

    public function replayForMission(EnterpriseMission $mission): array
    {
        return EnterpriseEvent::query()
            ->where('enterprise_mission_id', $mission->id)
            ->orderBy('created_at')
            ->get()
            ->map(function (EnterpriseEvent $event): array {
                $event->forceFill(['replayed_at' => now()])->save();

                return [
                    'event_type' => $event->event_type,
                    'payload' => $event->payload,
                    'created_at' => optional($event->created_at)?->toIso8601String(),
                ];
            })
            ->all();
    }
}
