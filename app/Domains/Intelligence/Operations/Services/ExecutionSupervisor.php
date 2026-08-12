<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Operations\Services;

use App\Domains\Intelligence\Operations\Models\MissionCheckpoint;
use App\Domains\Intelligence\Operations\Models\MissionExecution;

class ExecutionSupervisor
{
    public function checkpoint(MissionExecution $execution, array $snapshot = [], string $type = 'progress'): MissionCheckpoint
    {
        $execution->forceFill([
            'last_checkpoint_at' => now(),
            'snapshot_payload' => $snapshot,
        ])->save();

        return MissionCheckpoint::query()->create([
            'enterprise_mission_id' => $execution->enterprise_mission_id,
            'mission_execution_id' => $execution->id,
            'checkpoint_type' => $type,
            'status' => 'recorded',
            'snapshot_payload' => $snapshot,
            'recorded_at' => now(),
            'metadata' => [
                'resumable' => true,
                'replayable' => true,
            ],
        ]);
    }

    public function recover(MissionExecution $execution): MissionExecution
    {
        $execution->forceFill([
            'status' => 'recovered',
            'retry_count' => $execution->retry_count + 1,
            'last_checkpoint_at' => now(),
        ])->save();

        return $execution->fresh();
    }
}
