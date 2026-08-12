<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Commercial\Services;

use App\Domains\Intelligence\Commercial\Models\IntelligencePackage;

class PricingEstimator
{
    public function estimate(IntelligencePackage $package, string $deploymentMode): array
    {
        $base = (float) ($package->pricingRules()->first()?->base_price ?? 0);
        $deploymentMultiplier = match ($deploymentMode) {
            'shared_saas' => 1.00,
            'dedicated_saas' => 1.35,
            'private_cloud' => 1.55,
            'on_premise' => 1.85,
            default => 2.10,
        };

        return [
            'base_price' => $base,
            'deployment_mode' => $deploymentMode,
            'monthly_estimate' => round($base * $deploymentMultiplier, 2),
        ];
    }
}