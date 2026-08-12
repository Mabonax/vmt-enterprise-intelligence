<?php

declare(strict_types=1);

namespace App\Domains\Intelligence\AdminConsole\Services;

use App\Domains\Intelligence\AdminConsole\Enums\AdminConsoleHealthStatus;
use App\Domains\Intelligence\AdminConsole\Models\AdminConsoleHealthSnapshot;

class AdminConsoleHealthService
{
    public function __construct(
        private readonly AdminConsoleMetricsService $metrics,
        private readonly AdminConsoleReadinessService $readiness,
    ) {}

    public function capture(string $snapshotType = 'platform'): AdminConsoleHealthSnapshot
    {
        $signals = $this->signals();
        $status = $this->statusForSignals($signals);
        $score = $this->score($signals);
        $recommendations = $this->recommendations($signals, $status);

        return AdminConsoleHealthSnapshot::query()->create([
            'uuid' => (string) str()->uuid(),
            'snapshot_type' => $snapshotType,
            'overall_status' => $status->value,
            'score' => $score,
            'signals' => $signals,
            'recommendations' => $recommendations,
            'captured_at' => now(),
        ]);
    }

    public function latest(): array
    {
        $snapshot = AdminConsoleHealthSnapshot::query()->recent()->first() ?? $this->capture();

        return [
            'id' => $snapshot->id,
            'overall_status' => $snapshot->overall_status->value,
            'score' => $snapshot->score,
            'signals' => $snapshot->signals ?? [],
            'recommendations' => $snapshot->recommendations ?? [],
            'captured_at' => optional($snapshot->captured_at)->toIso8601String(),
        ];
    }

    public function calculateStatus(array $signals): AdminConsoleHealthStatus
    {
        return $this->statusForSignals($signals);
    }

    public function calculateScore(array $signals): float
    {
        return $this->score($signals);
    }

    private function signals(): array
    {
        $metrics = $this->metrics->summary();
        $readiness = $this->readiness->summary();

        return [
            'commercial_packages' => $metrics['total_commercial_packages'] > 0,
            'active_tenants' => $metrics['active_tenants'] > 0,
            'gateway_requests_flowing' => $metrics['gateway_requests_total'] >= 0,
            'gateway_errors' => $metrics['gateway_errors'] < 3,
            'provider_latency' => $metrics['provider_latency_ms'] < 5000,
            'ollama_available' => $metrics['ollama_status'] === 'healthy' || $metrics['ollama_status'] === 'unknown',
            'pending_provisions_pressure' => $metrics['pending_tenant_provisions'] < 5,
            'support_pressure' => $metrics['open_support_cases'] < 8,
            'usage_pressure' => $metrics['usage_quota_pressure'] < 5,
            'mission_health' => $metrics['operations_mission_health'] < 3,
            'agent_health' => $metrics['agent_execution_health'] < 3,
            'knowledge_health' => $metrics['knowledge_ingestion_health'] < 5,
            'compliance_warnings' => $metrics['compliance_policy_warnings'] < 3,
            'readiness_ok' => $readiness['overall_status'] === AdminConsoleHealthStatus::Healthy->value,
        ];
    }

    private function score(array $signals): float
    {
        $total = count($signals);
        $positive = count(array_filter($signals, static fn (bool $ok): bool => $ok));

        return round(($positive / max($total, 1)) * 100, 2);
    }

    private function statusForSignals(array $signals): AdminConsoleHealthStatus
    {
        $score = $this->score($signals);

        return match (true) {
            $score >= 90 => AdminConsoleHealthStatus::Healthy,
            $score >= 75 => AdminConsoleHealthStatus::Degraded,
            $score >= 50 => AdminConsoleHealthStatus::AtRisk,
            $score > 0 => AdminConsoleHealthStatus::Critical,
            default => AdminConsoleHealthStatus::Unknown,
        };
    }

    private function recommendations(array $signals, AdminConsoleHealthStatus $status): array
    {
        $recommendations = [];

        foreach ($signals as $key => $ok) {
            if (! $ok) {
                $recommendations[] = 'Review '.str($key)->replace('_', ' ')->lower()->toString().' and clear the underlying backlog.';
            }
        }

        if ($recommendations === []) {
            $recommendations[] = 'Platform health is stable. Keep daily health snapshots and alert triage active.';
        }

        if ($status === AdminConsoleHealthStatus::Critical) {
            $recommendations[] = 'Escalate unresolved blockers to the command center action queue immediately.';
        }

        return $recommendations;
    }
}
