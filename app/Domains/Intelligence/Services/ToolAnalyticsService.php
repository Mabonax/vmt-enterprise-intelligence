<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\Services;

use App\Domains\Intelligence\Tools\Models\ToolCost;
use App\Domains\Intelligence\Tools\Models\ToolUsage;
use Illuminate\Support\Facades\DB;

class ToolAnalyticsService
{
    /**
     * @return array<string, mixed>
     */
    public function summary(): array
    {
        return [
            'usage' => ToolUsage::query()
                ->select('enterprise_tool_id', DB::raw('count(*) as executions'), DB::raw('avg(duration_ms) as average_runtime'))
                ->groupBy('enterprise_tool_id')
                ->orderByDesc('executions')
                ->limit(5)
                ->get()
                ->toArray(),
            'costs' => ToolCost::query()
                ->select('enterprise_tool_id', DB::raw('sum(estimated_cost) as total_cost'))
                ->groupBy('enterprise_tool_id')
                ->orderByDesc('total_cost')
                ->limit(5)
                ->get()
                ->toArray(),
        ];
    }
}
