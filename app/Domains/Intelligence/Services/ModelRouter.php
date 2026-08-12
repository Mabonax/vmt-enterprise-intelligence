<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Services;

use App\Domains\Intelligence\Enums\ModelCapability;
use App\Domains\Intelligence\Models\ModelRoutingRule;
use Illuminate\Support\Arr;

class ModelRouter
{
    public function resolve(ModelCapability $capability, ?string $preferredProvider = null, ?string $preferredModel = null): array
    {
        $rule = ModelRoutingRule::query()
            ->where('capability', $capability)
            ->where('enabled', true)
            ->when($preferredProvider !== null, fn ($query) => $query->where('provider', $preferredProvider))
            ->when($preferredModel !== null, fn ($query) => $query->where('model', $preferredModel))
            ->orderBy('priority')
            ->first();

        if ($rule !== null) {
            return [
                'provider' => $rule->provider,
                'model' => $rule->model,
                'fallback_provider' => $rule->fallback_provider,
                'fallback_model' => $rule->fallback_model,
                'max_context_tokens' => $rule->max_context_tokens,
                'cost_tier' => $rule->cost_tier,
            ];
        }

        return [
            'provider' => $preferredProvider ?? (string) config('intelligence.default_provider'),
            'model' => $preferredModel ?? (string) config('intelligence.default_model'),
            'fallback_provider' => Arr::get(config('intelligence.model_routing.fallback'), 'provider'),
            'fallback_model' => Arr::get(config('intelligence.model_routing.fallback'), 'model'),
            'max_context_tokens' => (int) config('intelligence.token_limits.max_context'),
            'cost_tier' => 'standard',
        ];
    }
}
