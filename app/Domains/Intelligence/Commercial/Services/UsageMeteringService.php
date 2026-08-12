<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Services;

use App\Domains\Intelligence\Commercial\DTOs\UsageMeterRecordData;
use App\Domains\Intelligence\Commercial\Models\UsageLedgerEntry;
use App\Domains\Intelligence\Commercial\Models\UsageMeter;

class UsageMeteringService
{
    public function __construct(private readonly QuotaEnforcementService $quotaEnforcement) {}

    public function record(string $subscriptionId, UsageMeterRecordData $data): array
    {
        $meter = UsageMeter::query()
            ->where('intelligence_subscription_id', $subscriptionId)
            ->where('meter_key', $data->meterKey)
            ->firstOrFail();

        $meter->forceFill([
            'usage_total' => (float) $meter->usage_total + $data->quantity,
        ])->save();

        UsageLedgerEntry::query()->create([
            'usage_meter_id' => $meter->id,
            'entry_type' => 'consumption',
            'quantity' => $data->quantity,
            'status' => 'recorded',
            'recorded_at' => now(),
            'context' => $data->context,
        ]);

        return $this->quotaEnforcement->evaluate($meter->fresh());
    }
}