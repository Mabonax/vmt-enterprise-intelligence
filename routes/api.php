<?php

declare(strict_types=1);

use App\Http\Controllers\Intelligence\KnowledgeController;
use App\Http\Controllers\Intelligence\AdminConsoleController;
use App\Http\Controllers\Intelligence\CommercialController;
use App\Http\Controllers\Intelligence\MultiAgentController;
use App\Http\Controllers\Intelligence\OperationsController;
use App\Http\Controllers\Gateway\GatewayController;
use App\Http\Controllers\Gateway\GatewaySecurityController;
use Illuminate\Support\Facades\Route;

Route::get('/gateway/v1/health', [GatewayController::class, 'health'])->name('api.gateway.health');

Route::middleware(['gateway.correlation', 'gateway.client', 'gateway.resolve_tenant', 'gateway.replay', 'gateway.context'])->prefix('gateway/v1')->group(function (): void {
    Route::post('/chat', [GatewayController::class, 'chat'])->name('api.gateway.chat');
    Route::post('/summarise', [GatewayController::class, 'summarise'])->name('api.gateway.summarise');
    Route::post('/report', [GatewayController::class, 'report'])->name('api.gateway.report');
    Route::post('/translate', [GatewayController::class, 'translate'])->name('api.gateway.translate');
    Route::post('/classify', [GatewayController::class, 'classify'])->name('api.gateway.classify');
    Route::post('/search', [GatewayController::class, 'search'])->name('api.gateway.search');
    Route::post('/action', [GatewayController::class, 'action'])->name('api.gateway.action');
    Route::get('/requests/{gatewayRequest}', [GatewayController::class, 'show'])->name('api.gateway.requests.show');
    Route::post('/auth/keys', [GatewaySecurityController::class, 'createKey'])->name('api.gateway.auth.keys');
    Route::post('/auth/rotate', [GatewaySecurityController::class, 'rotateKey'])->name('api.gateway.auth.rotate');
    Route::post('/auth/revoke', [GatewaySecurityController::class, 'revokeKey'])->name('api.gateway.auth.revoke');
    Route::get('/clients', [GatewaySecurityController::class, 'clients'])->name('api.gateway.clients.index');
    Route::post('/clients', [GatewaySecurityController::class, 'storeClient'])->name('api.gateway.clients.store');
    Route::put('/clients/{client}', [GatewaySecurityController::class, 'updateClient'])->name('api.gateway.clients.update');
    Route::delete('/clients/{client}', [GatewaySecurityController::class, 'destroyClient'])->name('api.gateway.clients.destroy');
    Route::get('/tenants', [GatewaySecurityController::class, 'tenants'])->name('api.gateway.tenants.index');
    Route::get('/security/events', [GatewaySecurityController::class, 'securityEvents'])->name('api.gateway.security.events');
    Route::get('/security/usage', [GatewaySecurityController::class, 'usage'])->name('api.gateway.security.usage');
});

Route::middleware('auth:sanctum')->prefix('intelligence')->group(function (): void {
    Route::post('/agents', [MultiAgentController::class, 'store'])->name('api.intelligence.agents.store');
    Route::get('/roles', [MultiAgentController::class, 'roles'])->name('api.intelligence.roles');
    Route::post('/start', [MultiAgentController::class, 'start'])->name('api.intelligence.start');
    Route::post('/executions/{agentSession}/pause', [MultiAgentController::class, 'pause'])->name('api.intelligence.executions.pause');
    Route::post('/executions/{agentSession}/resume', [MultiAgentController::class, 'resume'])->name('api.intelligence.executions.resume');
    Route::post('/executions/{agentSession}/cancel', [MultiAgentController::class, 'cancel'])->name('api.intelligence.executions.cancel');
    Route::post('/executions/{agentSession}/delegate', [MultiAgentController::class, 'delegate'])->name('api.intelligence.executions.delegate');
    Route::post('/approvals/{agentApproval}/approve', [MultiAgentController::class, 'approve'])->name('api.intelligence.approvals.approve');
    Route::post('/approvals/{agentApproval}/reject', [MultiAgentController::class, 'reject'])->name('api.intelligence.approvals.reject');
    Route::get('/workflows', [MultiAgentController::class, 'workflows'])->name('api.intelligence.workflows');
    Route::get('/messages', [MultiAgentController::class, 'messages'])->name('api.intelligence.messages');
    Route::get('/teams', [MultiAgentController::class, 'teams'])->name('api.intelligence.teams');
    Route::get('/memory', [MultiAgentController::class, 'memory'])->name('api.intelligence.memory');
    Route::get('/reasoning', [MultiAgentController::class, 'reasoning'])->name('api.intelligence.reasoning');
    Route::get('/executions', [MultiAgentController::class, 'executions'])->name('api.intelligence.executions');

    Route::get('/operations/missions', [OperationsController::class, 'index'])->name('api.intelligence.operations.missions.index');
    Route::post('/operations/missions', [OperationsController::class, 'store'])->name('api.intelligence.operations.missions.store');
    Route::get('/operations/missions/{mission}', [OperationsController::class, 'show'])->name('api.intelligence.operations.missions.show');
    Route::put('/operations/missions/{mission}', [OperationsController::class, 'update'])->name('api.intelligence.operations.missions.update');
    Route::post('/operations/missions/{mission}/plan', [OperationsController::class, 'plan'])->name('api.intelligence.operations.missions.plan');
    Route::post('/operations/missions/{mission}/execute', [OperationsController::class, 'execute'])->name('api.intelligence.operations.missions.execute');
    Route::post('/operations/missions/{mission}/checkpoint', [OperationsController::class, 'checkpoint'])->name('api.intelligence.operations.missions.checkpoint');
    Route::post('/operations/missions/{mission}/recover', [OperationsController::class, 'recover'])->name('api.intelligence.operations.missions.recover');
    Route::get('/operations/monitoring', [OperationsController::class, 'monitoring'])->name('api.intelligence.operations.monitoring');
    Route::get('/operations/missions/{mission}/predictions', [OperationsController::class, 'predictions'])->name('api.intelligence.operations.predictions');
    Route::post('/operations/missions/{mission}/simulate', [OperationsController::class, 'simulate'])->name('api.intelligence.operations.simulations.store');
    Route::get('/operations/missions/{mission}/policies', [OperationsController::class, 'policies'])->name('api.intelligence.operations.policies');
    Route::get('/operations/missions/{mission}/compliance', [OperationsController::class, 'compliance'])->name('api.intelligence.operations.compliance');
    Route::get('/operations/missions/{mission}/approvals', [OperationsController::class, 'approvals'])->name('api.intelligence.operations.approvals');
    Route::post('/operations/approvals/{approval}/approve', [OperationsController::class, 'approve'])->name('api.intelligence.operations.approvals.approve');
    Route::post('/operations/approvals/{approval}/reject', [OperationsController::class, 'reject'])->name('api.intelligence.operations.approvals.reject');
    Route::get('/operations/kpis', [OperationsController::class, 'kpis'])->name('api.intelligence.operations.kpis');
    Route::get('/operations/missions/{mission}/decisions', [OperationsController::class, 'decisions'])->name('api.intelligence.operations.decisions');
    Route::post('/operations/missions/{mission}/decisions', [OperationsController::class, 'recordDecision'])->name('api.intelligence.operations.decisions.store');

    if (config('deployment.commercial_console_enabled', false)) {
    Route::get('/commercial/packages', [CommercialController::class, 'packages'])->name('api.intelligence.commercial.packages');
    Route::get('/commercial/tenants', [CommercialController::class, 'tenants'])->name('api.intelligence.commercial.tenants');
    Route::get('/commercial/provisioning', [CommercialController::class, 'provisioning'])->name('api.intelligence.commercial.provisioning');
    Route::post('/commercial/provisioning', [CommercialController::class, 'submitProvisioning'])->name('api.intelligence.commercial.provisioning.store');
    Route::post('/commercial/provisioning/{provisioningRequest}/activate', [CommercialController::class, 'activateProvisioning'])->name('api.intelligence.commercial.provisioning.activate');
    Route::get('/commercial/subscriptions', [CommercialController::class, 'subscriptions'])->name('api.intelligence.commercial.subscriptions');
    Route::post('/commercial/subscriptions', [CommercialController::class, 'createSubscription'])->name('api.intelligence.commercial.subscriptions.store');
    Route::post('/commercial/subscriptions/{subscription}/invoice', [CommercialController::class, 'generateInvoice'])->name('api.intelligence.commercial.subscriptions.invoice');
    Route::get('/commercial/usage', [CommercialController::class, 'usage'])->name('api.intelligence.commercial.usage');
    Route::post('/commercial/usage/{subscription}', [CommercialController::class, 'recordUsage'])->name('api.intelligence.commercial.usage.record');
    Route::get('/commercial/billing', [CommercialController::class, 'billing'])->name('api.intelligence.commercial.billing');
    Route::get('/commercial/proposals', [CommercialController::class, 'proposals'])->name('api.intelligence.commercial.proposals');
    Route::post('/commercial/proposals', [CommercialController::class, 'createProposal'])->name('api.intelligence.commercial.proposals.store');
    Route::post('/commercial/proposals/{proposal}/stage', [CommercialController::class, 'moveProposal'])->name('api.intelligence.commercial.proposals.stage');
    Route::post('/commercial/proposals/{proposal}/handover', [CommercialController::class, 'handover'])->name('api.intelligence.commercial.proposals.handover');
    Route::get('/commercial/deployments', [CommercialController::class, 'deployments'])->name('api.intelligence.commercial.deployments');
    Route::post('/commercial/deployments', [CommercialController::class, 'createRunbook'])->name('api.intelligence.commercial.deployments.store');
    Route::get('/commercial/support', [CommercialController::class, 'support'])->name('api.intelligence.commercial.support');
    Route::post('/commercial/support', [CommercialController::class, 'createSupportTicket'])->name('api.intelligence.commercial.support.store');
    Route::post('/commercial/support/{supportTicket}/stage', [CommercialController::class, 'moveSupportTicket'])->name('api.intelligence.commercial.support.stage');
    Route::get('/commercial/readiness', [CommercialController::class, 'readiness'])->name('api.intelligence.commercial.readiness');
    Route::post('/commercial/readiness/{tenant}', [CommercialController::class, 'runReadiness'])->name('api.intelligence.commercial.readiness.run');

    }
    Route::get('/admin-console/dashboard', [AdminConsoleController::class, 'dashboard'])->name('api.intelligence.admin-console.dashboard');
    Route::get('/admin-console/metrics', [AdminConsoleController::class, 'metrics'])->name('api.intelligence.admin-console.metrics');
    Route::get('/admin-console/alerts', [AdminConsoleController::class, 'alerts'])->name('api.intelligence.admin-console.alerts');
    Route::get('/admin-console/actions', [AdminConsoleController::class, 'actions'])->name('api.intelligence.admin-console.actions');
    Route::post('/admin-console/actions', [AdminConsoleController::class, 'requestAction'])->name('api.intelligence.admin-console.actions.store');
    Route::get('/admin-console/health', [AdminConsoleController::class, 'health'])->name('api.intelligence.admin-console.health');
    Route::get('/admin-console/readiness', [AdminConsoleController::class, 'readiness'])->name('api.intelligence.admin-console.readiness');
    Route::get('/admin-console/audit', [AdminConsoleController::class, 'audit'])->name('api.intelligence.admin-console.audit');
});

Route::middleware('auth:sanctum')->prefix('knowledge')->group(function (): void {
    Route::get('/search', [KnowledgeController::class, 'search'])->name('api.knowledge.search');
    Route::post('/documents', [KnowledgeController::class, 'upload'])->name('api.knowledge.documents.store');
    Route::get('/graph', [KnowledgeController::class, 'graph'])->name('api.knowledge.graph');
    Route::get('/memories', [KnowledgeController::class, 'memories'])->name('api.knowledge.memories');
    Route::get('/relationships', [KnowledgeController::class, 'relationships'])->name('api.knowledge.relationships');
    Route::get('/learning', [KnowledgeController::class, 'learningMetrics'])->name('api.knowledge.learning');
    Route::get('/analytics', [KnowledgeController::class, 'analytics'])->name('api.knowledge.analytics');
    Route::get('/embeddings', [KnowledgeController::class, 'embeddingStatus'])->name('api.knowledge.embeddings');
    Route::get('/health', [KnowledgeController::class, 'health'])->name('api.knowledge.health');
});
