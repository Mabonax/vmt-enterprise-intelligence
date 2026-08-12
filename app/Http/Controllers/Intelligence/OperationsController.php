<?php

declare(strict_types=1);

namespace App\Http\Controllers\Intelligence;

use App\Domains\Intelligence\Operations\Models\EnterpriseMission;
use App\Domains\Intelligence\Operations\Models\MissionApproval;
use App\Domains\Intelligence\Operations\Services\ApprovalWorkflowService;
use App\Domains\Intelligence\Operations\Services\DecisionAnalysisService;
use App\Domains\Intelligence\Operations\Services\ExecutiveOrchestrator;
use App\Domains\Intelligence\Operations\Services\ExecutionSupervisor;
use App\Domains\Intelligence\Operations\Services\KpiCalculationService;
use App\Domains\Intelligence\Operations\Services\MissionPlanner;
use App\Domains\Intelligence\Operations\Services\MissionReportingService;
use App\Domains\Intelligence\Operations\Services\OperationsMonitor;
use App\Domains\Intelligence\Operations\Services\PredictionEngine;
use App\Domains\Intelligence\Operations\Services\ScenarioSimulator;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OperationsController extends Controller
{
    public function __construct(
        private readonly ExecutiveOrchestrator $orchestrator,
        private readonly MissionPlanner $planner,
        private readonly ExecutionSupervisor $supervisor,
        private readonly OperationsMonitor $monitor,
        private readonly PredictionEngine $predictionEngine,
        private readonly ScenarioSimulator $simulator,
        private readonly KpiCalculationService $kpis,
        private readonly ApprovalWorkflowService $approvals,
        private readonly DecisionAnalysisService $decisions,
        private readonly MissionReportingService $reporting,
    ) {}

    public function index(): JsonResponse
    {
        return response()->json([
            'missions' => EnterpriseMission::query()->latest()->limit(25)->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'objective_summary' => ['required', 'string'],
            'priority' => ['nullable', 'string', 'max:50'],
            'owner_name' => ['nullable', 'string', 'max:255'],
            'budget_amount' => ['nullable', 'numeric', 'min:0'],
            'budget_currency' => ['nullable', 'string', 'max:12'],
            'deadline_at' => ['nullable', 'date'],
            'require_approval' => ['nullable', 'boolean'],
            'mission_payload' => ['nullable', 'array'],
            'metadata' => ['nullable', 'array'],
        ]);

        $mission = $this->orchestrator->launch($request->user(), $validated);

        return response()->json(['mission' => $mission], 201);
    }

    public function show(EnterpriseMission $mission): JsonResponse
    {
        return response()->json([
            'mission' => $mission->load([
                'objectives',
                'phases',
                'executions',
                'milestones',
                'checkpoints',
                'dependencies',
                'outcomes',
                'risks',
                'metrics',
                'planVersions',
                'approvals',
                'decisions',
                'predictions',
                'simulations',
                'policyEvaluations',
                'complianceReviews',
            ]),
            'report' => $this->reporting->report($mission),
        ]);
    }

    public function update(Request $request, EnterpriseMission $mission): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'objective_summary' => ['sometimes', 'string'],
            'priority' => ['sometimes', 'string', 'max:50'],
            'status' => ['sometimes', 'string', 'max:50'],
            'budget_amount' => ['sometimes', 'numeric', 'min:0'],
            'deadline_at' => ['sometimes', 'nullable', 'date'],
            'completion_percentage' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'metadata' => ['sometimes', 'array'],
        ]);

        $mission->fill($validated)->save();

        return response()->json(['mission' => $mission->fresh()]);
    }

    public function plan(EnterpriseMission $mission): JsonResponse
    {
        return response()->json([
            'plan' => $this->planner->plan($mission),
        ]);
    }

    public function execute(Request $request, EnterpriseMission $mission): JsonResponse
    {
        $validated = $request->validate([
            'mode' => ['nullable', 'string', 'max:50'],
        ]);

        return response()->json([
            'mission' => $this->orchestrator->execute($mission, (string) ($validated['mode'] ?? 'autonomous')),
        ]);
    }

    public function checkpoint(Request $request, EnterpriseMission $mission): JsonResponse
    {
        $validated = $request->validate([
            'snapshot' => ['nullable', 'array'],
            'type' => ['nullable', 'string', 'max:50'],
        ]);

        $execution = $mission->executions()->latest()->firstOrFail();

        return response()->json([
            'checkpoint' => $this->supervisor->checkpoint($execution, $validated['snapshot'] ?? [], (string) ($validated['type'] ?? 'progress')),
        ]);
    }

    public function recover(EnterpriseMission $mission): JsonResponse
    {
        $execution = $mission->executions()->latest()->firstOrFail();

        return response()->json([
            'execution' => $this->supervisor->recover($execution),
        ]);
    }

    public function monitoring(): JsonResponse
    {
        return response()->json(['monitoring' => $this->monitor->summary()]);
    }

    public function predictions(EnterpriseMission $mission): JsonResponse
    {
        return response()->json(['predictions' => $this->predictionEngine->predict($mission)]);
    }

    public function simulate(Request $request, EnterpriseMission $mission): JsonResponse
    {
        $validated = $request->validate([
            'scenario_type' => ['required', 'string', 'max:100'],
            'context' => ['nullable', 'array'],
        ]);

        return response()->json([
            'simulation' => $this->simulator->simulate($mission, $validated['scenario_type'], $validated['context'] ?? []),
        ]);
    }

    public function policies(EnterpriseMission $mission): JsonResponse
    {
        return response()->json(['policies' => $mission->policyEvaluations()->latest()->get()]);
    }

    public function compliance(EnterpriseMission $mission): JsonResponse
    {
        return response()->json(['compliance' => $mission->complianceReviews()->latest()->get()]);
    }

    public function approvals(EnterpriseMission $mission): JsonResponse
    {
        return response()->json(['approvals' => $mission->approvals()->latest()->get()]);
    }

    public function approve(Request $request, MissionApproval $approval): JsonResponse
    {
        $validated = $request->validate(['notes' => ['nullable', 'string']]);

        return response()->json([
            'approval' => $this->approvals->approve($approval, $request->user(), $validated['notes'] ?? null),
        ]);
    }

    public function reject(Request $request, MissionApproval $approval): JsonResponse
    {
        $validated = $request->validate(['notes' => ['nullable', 'string']]);

        return response()->json([
            'approval' => $this->approvals->reject($approval, $request->user(), $validated['notes'] ?? null),
        ]);
    }

    public function kpis(): JsonResponse
    {
        return response()->json(['kpis' => $this->kpis->snapshot()]);
    }

    public function decisions(EnterpriseMission $mission): JsonResponse
    {
        return response()->json(['decisions' => $mission->decisions()->latest()->get()]);
    }

    public function recordDecision(Request $request, EnterpriseMission $mission): JsonResponse
    {
        $validated = $request->validate([
            'decision_key' => ['required', 'string', 'max:255'],
            'reasoning' => ['nullable', 'string'],
            'chosen_option' => ['nullable', 'string'],
            'alternatives' => ['nullable', 'array'],
            'evidence' => ['nullable', 'array'],
            'context' => ['nullable', 'array'],
            'confidence_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'approvals' => ['nullable', 'array'],
            'outcome' => ['nullable', 'string'],
            'lessons_learned' => ['nullable', 'string'],
        ]);

        return response()->json([
            'decision' => $this->decisions->record($mission, $validated),
        ], 201);
    }
}
