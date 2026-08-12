<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\AdminConsole\Services;

use App\Domains\Intelligence\AdminConsole\Enums\AdminConsoleAlertSeverity;
use App\Domains\Intelligence\AdminConsole\Models\AdminConsoleAlert;
use App\Domains\Intelligence\Agents\Models\AgentSession;
use App\Domains\Intelligence\Gateway\Models\GatewayRequest;
use App\Domains\Intelligence\Gateway\Models\ProviderHealthCheck;
use App\Domains\Intelligence\Commercial\Models\DeploymentRunbook;
use App\Domains\Intelligence\Commercial\Models\IntelligencePackage;
use App\Domains\Intelligence\Commercial\Models\IntelligenceSubscription;
use App\Domains\Intelligence\Commercial\Models\IntelligenceTenant;
use App\Domains\Intelligence\Commercial\Models\ReleaseReadinessCheck;
use App\Domains\Intelligence\Commercial\Models\SupportTicket;
use App\Domains\Intelligence\Commercial\Models\TenantProvisioningRequest;
use App\Domains\Intelligence\Commercial\Models\UsageOverage;
use App\Domains\Intelligence\Commercial\Models\UsageQuota;
use App\Domains\Intelligence\Knowledge\Models\KnowledgeDocument;
use App\Domains\Intelligence\Operations\Models\EnterpriseMission;
use Illuminate\Support\Facades\DB;

class AdminConsoleMetricsService
{
    public function summary(): array
    {
        $openSupportCases = SupportTicket::query()->whereNotIn('status', ['resolved', 'closed'])->count();
        $usagePressure = UsageQuota::query()->where('warning_state', true)->count() + UsageOverage::query()->count();
        $missionHealth = EnterpriseMission::query()->whereIn('health_status', ['at_risk', 'critical'])->count();
        $agentExecutionHealth = AgentSession::query()->whereIn('status', ['failed', 'cancelled'])->count();
        $knowledgeIngestionHealth = KnowledgeDocument::query()->where('status', '!=', 'ready')->count();
        $complianceWarnings = AdminConsoleAlert::query()
            ->where('source_context', 'compliance')
            ->whereIn('severity', [AdminConsoleAlertSeverity::Warning->value, AdminConsoleAlertSeverity::Critical->value, AdminConsoleAlertSeverity::Blocker->value])
            ->unresolved()
            ->count();
        $gatewayStatusCounts = GatewayRequest::query()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->all();
        $latestProviderCheck = ProviderHealthCheck::query()->latest('checked_at')->first();
        $providerUsage = DB::table('provider_usage')
            ->selectRaw('COUNT(*) as interactions')
            ->selectRaw('COALESCE(SUM(input_tokens), 0) as input_tokens')
            ->selectRaw('COALESCE(SUM(output_tokens), 0) as output_tokens')
            ->selectRaw('AVG(duration_ms) as average_latency')
            ->first();

        return [
            'total_commercial_packages' => IntelligencePackage::query()->count(),
            'active_tenants' => IntelligenceTenant::query()->where('status', 'active')->count(),
            'pending_tenant_provisions' => TenantProvisioningRequest::query()->whereNotIn('status', ['active', 'completed'])->count(),
            'deployment_runs_by_status' => DeploymentRunbook::query()
                ->selectRaw('status, count(*) as aggregate')
                ->groupBy('status')
                ->pluck('aggregate', 'status')
                ->all(),
            'open_support_cases' => $openSupportCases,
            'usage_quota_pressure' => $usagePressure,
            'operations_mission_health' => $missionHealth,
            'agent_execution_health' => $agentExecutionHealth,
            'knowledge_ingestion_health' => $knowledgeIngestionHealth,
            'compliance_policy_warnings' => $complianceWarnings,
            'active_subscriptions' => IntelligenceSubscription::query()->where('status', 'active')->count(),
            'readiness_reviews_pending' => ReleaseReadinessCheck::query()->where('status', 'review_required')->count(),
            'gateway_requests_total' => GatewayRequest::query()->count(),
            'gateway_requests_by_status' => $gatewayStatusCounts,
            'gateway_errors' => (int) ($gatewayStatusCounts['failed'] ?? 0),
            'provider_usage_interactions' => (int) ($providerUsage->interactions ?? 0),
            'provider_usage_input_tokens' => (int) ($providerUsage->input_tokens ?? 0),
            'provider_usage_output_tokens' => (int) ($providerUsage->output_tokens ?? 0),
            'provider_latency_ms' => (int) round((float) ($latestProviderCheck?->latency_ms ?? $providerUsage->average_latency ?? 0)),
            'ollama_status' => $latestProviderCheck?->status ?? 'unknown',
            'ollama_models_available' => (int) ($latestProviderCheck?->available_models ?? 0),
            'cloud_egress_enabled' => config('gateway.cloud_providers_enabled') ? 1 : 0,
        ];
    }
}
