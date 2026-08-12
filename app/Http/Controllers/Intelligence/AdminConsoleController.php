<?php

declare(strict_types=1);

namespace App\Http\Controllers\Intelligence;

use App\Domains\Intelligence\AdminConsole\Enums\AdminConsoleAudienceType;
use App\Domains\Intelligence\AdminConsole\Enums\AdminConsoleAlertSeverity;
use App\Domains\Intelligence\AdminConsole\Models\AdminConsoleAction;
use App\Domains\Intelligence\AdminConsole\Models\AdminConsoleAlert;
use App\Domains\Intelligence\AdminConsole\Models\AdminConsoleAuditEvent;
use App\Domains\Intelligence\AdminConsole\Models\AdminConsoleHealthSnapshot;
use App\Domains\Intelligence\AdminConsole\Services\AdminConsoleActionService;
use App\Domains\Intelligence\AdminConsole\Services\AdminConsoleAlertService;
use App\Domains\Intelligence\AdminConsole\Services\AdminConsoleAuditService;
use App\Domains\Intelligence\AdminConsole\Services\AdminConsoleDashboardService;
use App\Domains\Intelligence\AdminConsole\Services\AdminConsoleHealthService;
use App\Domains\Intelligence\AdminConsole\Services\AdminConsoleNavigationService;
use App\Domains\Intelligence\AdminConsole\Services\AdminConsoleReadinessService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminConsoleController extends Controller
{
    public function __construct(
        private readonly AdminConsoleDashboardService $dashboards,
        private readonly AdminConsoleAlertService $alerts,
        private readonly AdminConsoleActionService $actions,
        private readonly AdminConsoleAuditService $audit,
        private readonly AdminConsoleHealthService $health,
        private readonly AdminConsoleReadinessService $readiness,
    ) {}

    public function index(): Response
    {
        return $this->page('Index', 'index', AdminConsoleAudienceType::Operator, 'Enterprise admin console');
    }

    public function dashboard(): JsonResponse
    {
        $this->authorize('viewAny', AdminConsoleAlert::class);

        return response()->json($this->dashboards->build('command-center', AdminConsoleAudienceType::Operator)->toArray());
    }

    public function executive(): Response
    {
        return $this->page('Executive', 'executive', AdminConsoleAudienceType::Executive, 'Business posture and strategic risk');
    }

    public function operations(): Response
    {
        return $this->page('Operations', 'operations', AdminConsoleAudienceType::Operator, 'Operational alerts, approvals, and execution');
    }

    public function commercial(): Response
    {
        return $this->page('Commercial', 'commercial', AdminConsoleAudienceType::Commercial, 'Commercial growth and delivery readiness');
    }

    public function technical(): Response
    {
        return $this->page('Technical', 'technical', AdminConsoleAudienceType::Technical, 'Tooling, providers, and system health');
    }

    public function support(): Response
    {
        return $this->page('Support', 'support', AdminConsoleAudienceType::Support, 'Support queues and tenant impact');
    }

    public function compliance(): Response
    {
        return $this->page('Compliance', 'compliance', AdminConsoleAudienceType::Compliance, 'Policy warnings and audit visibility');
    }

    public function alerts(): Response|JsonResponse
    {
        $this->authorize('viewAny', AdminConsoleAlert::class);

        if (request()->expectsJson()) {
            return response()->json(['alerts' => $this->alerts->feed()]);
        }

        return Inertia::render('Intelligence/AdminConsole/Alerts', $this->sharedProps('Alerts', 'Open and historical command center alerts'));
    }

    public function acknowledgeAlert(Request $request, AdminConsoleAlert $alert): RedirectResponse
    {
        $this->authorize('manageAlerts', AdminConsoleAlert::class);

        $this->alerts->acknowledge($alert, $request->user(), $request);

        return back();
    }

    public function resolveAlert(Request $request, AdminConsoleAlert $alert): RedirectResponse
    {
        $this->authorize('manageAlerts', AdminConsoleAlert::class);

        $this->alerts->resolve($alert, $request->user(), $request);

        return back();
    }

    public function dismissAlert(Request $request, AdminConsoleAlert $alert): RedirectResponse
    {
        $this->authorize('manageAlerts', AdminConsoleAlert::class);

        $this->alerts->dismiss($alert, $request->user(), $request);

        return back();
    }

    public function actions(): Response|JsonResponse
    {
        $this->authorize('viewAny', AdminConsoleAction::class);

        if (request()->expectsJson()) {
            return response()->json(['actions' => $this->actions->queue()]);
        }

        return Inertia::render('Intelligence/AdminConsole/Actions', $this->sharedProps('Action Queue', 'Operator requests, approvals, and execution results'));
    }

    public function requestAction(Request $request): RedirectResponse|JsonResponse
    {
        $this->authorize('manageActions', AdminConsoleAction::class);

        $validated = $request->validate([
            'action_type' => ['required', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'target_context' => ['nullable', 'string', 'max:100'],
            'target_type' => ['nullable', 'string', 'max:150'],
            'target_id' => ['nullable', 'string', 'max:150'],
            'payload' => ['nullable', 'array'],
        ]);

        $action = $this->actions->request($request->user(), $validated, $request);

        if ($request->expectsJson()) {
            return response()->json(['action' => $action], 201);
        }

        return back();
    }

    public function approveAction(Request $request, AdminConsoleAction $action): RedirectResponse
    {
        $this->authorize('manageActions', AdminConsoleAction::class);

        $this->actions->approve($action, $request->user(), $request);

        return back();
    }

    public function executeAction(Request $request, AdminConsoleAction $action): RedirectResponse
    {
        $this->authorize('manageActions', AdminConsoleAction::class);

        $this->actions->execute($action, $request->user(), $request);

        return back();
    }

    public function audit(Request $request): Response|JsonResponse
    {
        $this->authorize('viewAudit', AdminConsoleAuditEvent::class);

        if ($request->expectsJson()) {
            return response()->json(['timeline' => $this->audit->timeline($request->string('search')->toString() ?: null)]);
        }

        return Inertia::render('Intelligence/AdminConsole/Audit', $this->sharedProps('Audit Timeline', 'Administrative activity, responses, and change traceability'));
    }

    public function health(): Response|JsonResponse
    {
        $this->authorize('viewHealth', AdminConsoleHealthSnapshot::class);

        if (request()->expectsJson()) {
            return response()->json(['health' => $this->health->latest()]);
        }

        return Inertia::render('Intelligence/AdminConsole/Health', $this->sharedProps('Platform Health', 'Latest platform health snapshot and recommendations'));
    }

    public function readiness(): Response|JsonResponse
    {
        $this->authorize('viewReadiness', AdminConsoleHealthSnapshot::class);

        if (request()->expectsJson()) {
            return response()->json(['readiness' => $this->readiness->summary()]);
        }

        return Inertia::render('Intelligence/AdminConsole/Readiness', $this->sharedProps('Readiness', 'Blockers, next actions, and launch posture'));
    }

    public function metrics(): JsonResponse
    {
        $this->authorize('viewAny', AdminConsoleAlert::class);

        return response()->json(['metrics' => $this->dashboards->build('command-center', AdminConsoleAudienceType::Operator)->metrics]);
    }

    private function page(string $component, string $slug, AdminConsoleAudienceType $audience, string $description): Response
    {
        $this->authorize('viewAny', AdminConsoleAlert::class);

        return Inertia::render('Intelligence/AdminConsole/'.$component, $this->sharedProps(
            match ($component) {
                'Index' => 'Command Center',
                default => $component.' View',
            },
            $description,
            $this->dashboards->build($slug, $audience)->toArray(),
        ));
    }

    private function sharedProps(string $title, string $description, ?array $dashboardPayload = null): array
    {
        $payload = $dashboardPayload ?? $this->dashboards->build('index', AdminConsoleAudienceType::Operator)->toArray();

        return [
            'page' => [
                'title' => $title,
                'description' => $description,
                'eyebrow' => 'Enterprise admin command center',
            ],
            'navigation' => AdminConsoleNavigationService::items(),
            'dashboard' => $payload['dashboard'],
            'metrics' => $payload['metrics'],
            'widgets' => $payload['widgets'],
            'alerts' => $this->alerts->feed(),
            'actions' => $this->actions->queue(),
            'health' => $this->health->latest(),
            'readiness' => $this->readiness->summary(),
            'auditTimeline' => $this->audit->timeline(),
            'forms' => [
                'requestAction' => route('intelligence.admin-console.actions.request'),
            ],
            'routes' => [
                'alerts' => [
                    'acknowledge' => route('intelligence.admin-console.alerts.acknowledge', ['alert' => '__ALERT__']),
                    'resolve' => route('intelligence.admin-console.alerts.resolve', ['alert' => '__ALERT__']),
                    'dismiss' => route('intelligence.admin-console.alerts.dismiss', ['alert' => '__ALERT__']),
                ],
                'actions' => [
                    'approve' => route('intelligence.admin-console.actions.approve', ['action' => '__ACTION__']),
                    'execute' => route('intelligence.admin-console.actions.execute', ['action' => '__ACTION__']),
                ],
            ],
            'summaryCards' => [
                ['label' => 'Active tenants', 'value' => (string) ($payload['metrics']['active_tenants'] ?? 0)],
                ['label' => 'Gateway requests', 'value' => (string) ($payload['metrics']['gateway_requests_total'] ?? 0)],
                ['label' => 'Gateway errors', 'value' => (string) ($payload['metrics']['gateway_errors'] ?? 0)],
                ['label' => 'Provider usage', 'value' => (string) ($payload['metrics']['provider_usage_interactions'] ?? 0)],
            ],
        ];
    }
}
