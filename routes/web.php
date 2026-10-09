<?php

declare(strict_types=1);

use App\Http\Controllers\Intelligence\AdminConsoleController;
use App\Http\Controllers\Intelligence\AgentController;
use App\Http\Controllers\Intelligence\ConnectedApplicationsController;
use App\Http\Controllers\Intelligence\PromptTemplateController;
use App\Http\Controllers\Intelligence\RuntimeController;
use App\Http\Controllers\Intelligence\WorkspaceController;
use App\Http\Controllers\Platform\DashboardController;
use App\Http\Controllers\Platform\PlatformPageController;
use App\Http\Controllers\ProfileController;
use App\Support\Navigation\VipNavigation;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('home');

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/intelligence', WorkspaceController::class)
        ->defaults('page', 'intelligence-dashboard')
        ->name('intelligence.dashboard');
    Route::get('/intelligence/conversations', WorkspaceController::class)
        ->defaults('page', 'intelligence-conversations')
        ->name('intelligence.conversations');
    Route::get('/intelligence/providers', WorkspaceController::class)
        ->defaults('page', 'intelligence-providers')
        ->name('intelligence.providers');
    Route::get('/intelligence/agents', WorkspaceController::class)
        ->defaults('page', 'intelligence-agents')
        ->name('intelligence.agents');
    Route::get('/intelligence/tools', WorkspaceController::class)
        ->defaults('page', 'intelligence-tools')
        ->name('intelligence.tools');
    Route::get('/intelligence/marketplace', WorkspaceController::class)
        ->defaults('page', 'intelligence-marketplace')
        ->name('intelligence.marketplace');
    Route::get('/intelligence/connectors', WorkspaceController::class)
        ->defaults('page', 'intelligence-connectors')
        ->name('intelligence.connectors');
    Route::get('/intelligence/credentials', WorkspaceController::class)
        ->defaults('page', 'intelligence-credentials')
        ->name('intelligence.credentials');
    Route::get('/intelligence/analytics', WorkspaceController::class)
        ->defaults('page', 'intelligence-analytics')
        ->name('intelligence.analytics');
    Route::get('/intelligence/health', WorkspaceController::class)
        ->defaults('page', 'intelligence-health')
        ->name('intelligence.health');
    Route::get('/intelligence/execution-graph', WorkspaceController::class)
        ->defaults('page', 'intelligence-execution-graph')
        ->name('intelligence.execution-graph');
    Route::get('/intelligence/execution-history', WorkspaceController::class)
        ->defaults('page', 'intelligence-execution-history')
        ->name('intelligence.execution-history');
    Route::get('/intelligence/testing', WorkspaceController::class)
        ->defaults('page', 'intelligence-testing')
        ->name('intelligence.testing');
    Route::get('/intelligence/sdk', WorkspaceController::class)
        ->defaults('page', 'intelligence-sdk')
        ->name('intelligence.sdk');
    Route::get('/intelligence/costs', WorkspaceController::class)
        ->defaults('page', 'intelligence-costs')
        ->name('intelligence.costs');
    Route::get('/intelligence/knowledge', WorkspaceController::class)
        ->defaults('page', 'intelligence-knowledge-dashboard')
        ->name('intelligence.knowledge-dashboard');
    Route::get('/intelligence/knowledge/search', WorkspaceController::class)
        ->defaults('page', 'intelligence-enterprise-search')
        ->name('intelligence.enterprise-search');
    Route::get('/intelligence/knowledge/graph', WorkspaceController::class)
        ->defaults('page', 'intelligence-knowledge-graph')
        ->name('intelligence.knowledge-graph');
    Route::get('/intelligence/knowledge/memories', WorkspaceController::class)
        ->defaults('page', 'intelligence-memory-explorer')
        ->name('intelligence.memory-explorer');
    Route::get('/intelligence/knowledge/documents', WorkspaceController::class)
        ->defaults('page', 'intelligence-document-library')
        ->name('intelligence.document-library');
    Route::get('/intelligence/knowledge/collections', WorkspaceController::class)
        ->defaults('page', 'intelligence-collections')
        ->name('intelligence.collections');
    Route::get('/intelligence/knowledge/embeddings', WorkspaceController::class)
        ->defaults('page', 'intelligence-embeddings')
        ->name('intelligence.embeddings');
    Route::get('/intelligence/knowledge/relationships', WorkspaceController::class)
        ->defaults('page', 'intelligence-relationships')
        ->name('intelligence.relationships');
    Route::get('/intelligence/knowledge/learning', WorkspaceController::class)
        ->defaults('page', 'intelligence-learning')
        ->name('intelligence.learning');
    Route::get('/intelligence/knowledge/timeline', WorkspaceController::class)
        ->defaults('page', 'intelligence-knowledge-timeline')
        ->name('intelligence.knowledge-timeline');
    Route::get('/intelligence/knowledge/analytics', WorkspaceController::class)
        ->defaults('page', 'intelligence-knowledge-analytics')
        ->name('intelligence.knowledge-analytics');
    Route::get('/intelligence/knowledge/health', WorkspaceController::class)
        ->defaults('page', 'intelligence-knowledge-health')
        ->name('intelligence.knowledge-health');
    Route::get('/intelligence/enterprise-agents', WorkspaceController::class)
        ->defaults('page', 'intelligence-enterprise-agents')
        ->name('intelligence.enterprise-agents');
    Route::get('/intelligence/agent-teams', WorkspaceController::class)
        ->defaults('page', 'intelligence-agent-teams')
        ->name('intelligence.agent-teams');
    Route::get('/intelligence/executions', WorkspaceController::class)
        ->defaults('page', 'intelligence-executions')
        ->name('intelligence.executions');
    Route::get('/intelligence/reasoning', WorkspaceController::class)
        ->defaults('page', 'intelligence-reasoning')
        ->name('intelligence.reasoning');
    Route::get('/intelligence/approvals', WorkspaceController::class)
        ->defaults('page', 'intelligence-approvals')
        ->name('intelligence.approvals');
    Route::get('/intelligence/communication', WorkspaceController::class)
        ->defaults('page', 'intelligence-communication')
        ->name('intelligence.communication');
    Route::get('/intelligence/shared-memory', WorkspaceController::class)
        ->defaults('page', 'intelligence-shared-memory')
        ->name('intelligence.shared-memory');
    Route::get('/intelligence/performance', WorkspaceController::class)
        ->defaults('page', 'intelligence-performance')
        ->name('intelligence.performance');
    Route::get('/intelligence/quality', WorkspaceController::class)
        ->defaults('page', 'intelligence-quality')
        ->name('intelligence.quality');
    Route::get('/intelligence/decision-records', WorkspaceController::class)
        ->defaults('page', 'intelligence-decision-records')
        ->name('intelligence.decision-records');
    Route::get('/intelligence/monitoring', WorkspaceController::class)
        ->defaults('page', 'intelligence-monitoring')
        ->name('intelligence.monitoring');
    Route::get('/intelligence/operations', WorkspaceController::class)
        ->defaults('page', 'intelligence-executive-dashboard')
        ->name('intelligence.operations.executive-dashboard');
    Route::get('/intelligence/operations/missions', WorkspaceController::class)
        ->defaults('page', 'intelligence-enterprise-missions')
        ->name('intelligence.operations.enterprise-missions');
    Route::get('/intelligence/operations/timeline', WorkspaceController::class)
        ->defaults('page', 'intelligence-mission-timeline')
        ->name('intelligence.operations.mission-timeline');
    Route::get('/intelligence/operations/centre', WorkspaceController::class)
        ->defaults('page', 'intelligence-operations-centre')
        ->name('intelligence.operations.operations-centre');
    Route::get('/intelligence/operations/executions', WorkspaceController::class)
        ->defaults('page', 'intelligence-execution-centre')
        ->name('intelligence.operations.execution-centre');
    Route::get('/intelligence/operations/planner', WorkspaceController::class)
        ->defaults('page', 'intelligence-mission-planner')
        ->name('intelligence.operations.mission-planner');
    Route::get('/intelligence/operations/marketplace', WorkspaceController::class)
        ->defaults('page', 'intelligence-agent-marketplace')
        ->name('intelligence.operations.agent-marketplace');
    Route::get('/intelligence/operations/simulations', WorkspaceController::class)
        ->defaults('page', 'intelligence-simulation-studio')
        ->name('intelligence.operations.simulation-studio');
    Route::get('/intelligence/operations/predictions', WorkspaceController::class)
        ->defaults('page', 'intelligence-predictions')
        ->name('intelligence.operations.predictions');
    Route::get('/intelligence/operations/compliance', WorkspaceController::class)
        ->defaults('page', 'intelligence-operations-compliance')
        ->name('intelligence.operations.compliance');
    Route::get('/intelligence/operations/policies', WorkspaceController::class)
        ->defaults('page', 'intelligence-operations-policies')
        ->name('intelligence.operations.policies');
    Route::get('/intelligence/operations/approvals', WorkspaceController::class)
        ->defaults('page', 'intelligence-operations-approvals')
        ->name('intelligence.operations.approvals');
    Route::get('/intelligence/operations/monitoring', WorkspaceController::class)
        ->defaults('page', 'intelligence-enterprise-monitoring')
        ->name('intelligence.operations.enterprise-monitoring');
    Route::get('/intelligence/operations/decisions', WorkspaceController::class)
        ->defaults('page', 'intelligence-operations-decision-records')
        ->name('intelligence.operations.decision-records');
    Route::get('/intelligence/operations/kpis', WorkspaceController::class)
        ->defaults('page', 'intelligence-enterprise-kpis')
        ->name('intelligence.operations.enterprise-kpis');
    Route::get('/intelligence/operations/autonomy', WorkspaceController::class)
        ->defaults('page', 'intelligence-autonomy-analytics')
        ->name('intelligence.operations.autonomy-analytics');
    if (config('deployment.commercial_console_enabled', false)) {
    Route::get('/intelligence/commercial/packages', WorkspaceController::class)
        ->defaults('page', 'intelligence-commercial-packages')
        ->name('intelligence.commercial.packages');
    Route::get('/intelligence/commercial/tenants', WorkspaceController::class)
        ->defaults('page', 'intelligence-commercial-tenants')
        ->name('intelligence.commercial.tenants');
    Route::get('/intelligence/commercial/provisioning', WorkspaceController::class)
        ->defaults('page', 'intelligence-commercial-provisioning')
        ->name('intelligence.commercial.provisioning');
    Route::get('/intelligence/commercial/subscriptions', WorkspaceController::class)
        ->defaults('page', 'intelligence-commercial-subscriptions')
        ->name('intelligence.commercial.subscriptions');
    Route::get('/intelligence/commercial/usage', WorkspaceController::class)
        ->defaults('page', 'intelligence-commercial-usage')
        ->name('intelligence.commercial.usage');
    Route::get('/intelligence/commercial/billing', WorkspaceController::class)
        ->defaults('page', 'intelligence-commercial-billing')
        ->name('intelligence.commercial.billing');
    Route::get('/intelligence/commercial/proposals', WorkspaceController::class)
        ->defaults('page', 'intelligence-commercial-proposals')
        ->name('intelligence.commercial.proposals');
    Route::get('/intelligence/commercial/deployments', WorkspaceController::class)
        ->defaults('page', 'intelligence-commercial-deployments')
        ->name('intelligence.commercial.deployments');
    Route::get('/intelligence/commercial/support', WorkspaceController::class)
        ->defaults('page', 'intelligence-commercial-support')
        ->name('intelligence.commercial.support');
    Route::get('/intelligence/commercial/readiness', WorkspaceController::class)
        ->defaults('page', 'intelligence-commercial-readiness')
        ->name('intelligence.commercial.readiness');
    Route::get('/intelligence/commercial/settings', WorkspaceController::class)
        ->defaults('page', 'intelligence-commercial-settings')
        ->name('intelligence.commercial.settings');
    }
    Route::get('/intelligence/admin-console', [AdminConsoleController::class, 'index'])->name('intelligence.admin-console.index');
    Route::get('/intelligence/admin-console/executive', [AdminConsoleController::class, 'executive'])->name('intelligence.admin-console.executive');
    Route::get('/intelligence/admin-console/operations', [AdminConsoleController::class, 'operations'])->name('intelligence.admin-console.operations');
    Route::get('/intelligence/admin-console/commercial', [AdminConsoleController::class, 'commercial'])->name('intelligence.admin-console.commercial');
    Route::get('/intelligence/admin-console/technical', [AdminConsoleController::class, 'technical'])->name('intelligence.admin-console.technical');
    Route::get('/intelligence/admin-console/support', [AdminConsoleController::class, 'support'])->name('intelligence.admin-console.support');
    Route::get('/intelligence/admin-console/compliance', [AdminConsoleController::class, 'compliance'])->name('intelligence.admin-console.compliance');
    Route::get('/intelligence/admin-console/alerts', [AdminConsoleController::class, 'alerts'])->name('intelligence.admin-console.alerts');
    Route::post('/intelligence/admin-console/alerts/{alert}/acknowledge', [AdminConsoleController::class, 'acknowledgeAlert'])->name('intelligence.admin-console.alerts.acknowledge');
    Route::post('/intelligence/admin-console/alerts/{alert}/resolve', [AdminConsoleController::class, 'resolveAlert'])->name('intelligence.admin-console.alerts.resolve');
    Route::post('/intelligence/admin-console/alerts/{alert}/dismiss', [AdminConsoleController::class, 'dismissAlert'])->name('intelligence.admin-console.alerts.dismiss');
    Route::get('/intelligence/admin-console/actions', [AdminConsoleController::class, 'actions'])->name('intelligence.admin-console.actions');
    Route::post('/intelligence/admin-console/actions', [AdminConsoleController::class, 'requestAction'])->name('intelligence.admin-console.actions.request');
    Route::post('/intelligence/admin-console/actions/{action}/approve', [AdminConsoleController::class, 'approveAction'])->name('intelligence.admin-console.actions.approve');
    Route::post('/intelligence/admin-console/actions/{action}/execute', [AdminConsoleController::class, 'executeAction'])->name('intelligence.admin-console.actions.execute');
    Route::get('/intelligence/admin-console/audit', [AdminConsoleController::class, 'audit'])->name('intelligence.admin-console.audit');
    Route::get('/intelligence/admin-console/health', [AdminConsoleController::class, 'health'])->name('intelligence.admin-console.health');
    Route::get('/intelligence/admin-console/readiness', [AdminConsoleController::class, 'readiness'])->name('intelligence.admin-console.readiness');
    Route::get('/intelligence/admin-console/connected-applications', [ConnectedApplicationsController::class, 'index'])->name('intelligence.admin-console.connected-applications.index');
    Route::post('/intelligence/admin-console/connected-applications/organizations', [ConnectedApplicationsController::class, 'storeOrganization'])->name('intelligence.admin-console.connected-applications.organizations.store');
    Route::post('/intelligence/admin-console/connected-applications/tenants', [ConnectedApplicationsController::class, 'storeTenant'])->name('intelligence.admin-console.connected-applications.tenants.store');
    Route::post('/intelligence/admin-console/connected-applications/clients', [ConnectedApplicationsController::class, 'storeClient'])->name('intelligence.admin-console.connected-applications.clients.store');
    Route::post('/intelligence/admin-console/connected-applications/clients/{client}/keys', [ConnectedApplicationsController::class, 'issueKey'])->name('intelligence.admin-console.connected-applications.clients.keys.issue');
    Route::post('/intelligence/admin-console/connected-applications/clients/{client}/keys/{keyIdentifier}/revoke', [ConnectedApplicationsController::class, 'revokeKey'])->name('intelligence.admin-console.connected-applications.clients.keys.revoke');
    Route::get('/intelligence/agent-settings', WorkspaceController::class)
        ->defaults('page', 'intelligence-agent-settings')
        ->name('intelligence.agent-settings');
    Route::get('/intelligence/usage', WorkspaceController::class)
        ->defaults('page', 'intelligence-usage')
        ->name('intelligence.usage');
    Route::get('/intelligence/settings', WorkspaceController::class)
        ->defaults('page', 'intelligence-settings')
        ->name('intelligence.settings');
    Route::get('/intelligence/planner', WorkspaceController::class)
        ->defaults('page', 'intelligence-planner')
        ->name('intelligence.planner');
    Route::get('/intelligence/memory', WorkspaceController::class)
        ->defaults('page', 'intelligence-memory')
        ->name('intelligence.memory');
    Route::get('/intelligence/prompts', WorkspaceController::class)
        ->defaults('page', 'intelligence-prompts')
        ->name('intelligence.prompts');
    Route::get('/intelligence/routing', WorkspaceController::class)
        ->defaults('page', 'intelligence-routing')
        ->name('intelligence.routing');
    Route::get('/intelligence/traces', WorkspaceController::class)
        ->defaults('page', 'intelligence-traces')
        ->name('intelligence.traces');
    Route::get('/intelligence/workflows', WorkspaceController::class)
        ->defaults('page', 'intelligence-workflows')
        ->name('intelligence.workflows');
    Route::get('/intelligence/diagnostics', WorkspaceController::class)
        ->defaults('page', 'intelligence-diagnostics')
        ->name('intelligence.diagnostics');

    Route::post('/intelligence/agents', [AgentController::class, 'store'])->name('intelligence.agent.store');
    Route::put('/intelligence/agents/{agent}', [AgentController::class, 'update'])->name('intelligence.agent.update');
    Route::post('/intelligence/prompts', [PromptTemplateController::class, 'store'])->name('intelligence.prompt.store');
    Route::put('/intelligence/prompts/{promptTemplate}', [PromptTemplateController::class, 'update'])->name('intelligence.prompt.update');

    Route::post('/intelligence/runtime/execute', [RuntimeController::class, 'execute'])->name('intelligence.runtime.execute');
    Route::get('/intelligence/runtime/history', [RuntimeController::class, 'history'])->name('intelligence.runtime.history');
    Route::get('/intelligence/runtime/replay/{executionTrace}', [RuntimeController::class, 'replay'])->name('intelligence.runtime.replay');
    Route::post('/intelligence/runtime/cancel/{executionTrace}', [RuntimeController::class, 'cancel'])->name('intelligence.runtime.cancel');
    Route::post('/intelligence/runtime/background', [RuntimeController::class, 'background'])->name('intelligence.runtime.background');
    Route::get('/intelligence/runtime/background/{backgroundTask}', [RuntimeController::class, 'backgroundStatus'])->name('intelligence.runtime.background-status');
    Route::get('/intelligence/runtime/stream/{executionTrace}', [RuntimeController::class, 'stream'])->name('intelligence.runtime.stream');

    foreach (VipNavigation::pages() as $page) {
        Route::get("/{$page['slug']}", PlatformPageController::class)
            ->defaults('page', $page['slug'])
            ->name($page['route']);
    }

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
