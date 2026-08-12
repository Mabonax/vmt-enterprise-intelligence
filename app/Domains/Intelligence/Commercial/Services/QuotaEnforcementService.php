<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Services;

use App\Domains\Intelligence\Commercial\Models\UsageMeter;
use App\Domains\Intelligence\Commercial\Models\UsageOverage;
use App\Domains\Intelligence\Commercial\Models\UsageQuota;

class QuotaEnforcementService
{
    public function evaluate(UsageMeter $meter): array
    {
        $limit = max((float) $meter->usage_limit, 1);
        $percentage = round(((float) $meter->usage_total / $limit) * 100, 2);
        $warning = $percentage >= (float) $meter->warning_threshold;
        $hardBlocked = $meter->hard_limit && $percentage > 100;

        $quota = UsageQuota::query()->where('intelligence_subscription_id', $meter->intelligence_subscription_id)
            ->where('quota_key', $meter->meter_key)
            ->first();

        if ($quota !== null) {
            $quota->forceFill([
                'consumed' => $meter->usage_total,
                'warning_state' => $warning,
            ])->save();
        }

        if ($percentage > 100) {
            UsageOverage::query()->create([
                'intelligence_subscription_id' => $meter->intelligence_subscription_id,
                'usage_meter_id' => $meter->id,
                'overage_key' => $meter->meter_key,
                'quantity' => max((float) $meter->usage_total - (float) $meter->usage_limit, 0),
                'status' => $hardBlocked ? 'blocked' : 'warning',
                'metadata' => ['percentage' => $percentage],
            ]);
        }

        return [
            'meter_key' => $meter->meter_key,
            'usage_total' => (float) $meter->usage_total,
            'usage_limit' => (float) $meter->usage_limit,
            'percentage' => $percentage,
            'warning' => $warning,
            'hard_blocked' => $hardBlocked,
        ];
    }
}