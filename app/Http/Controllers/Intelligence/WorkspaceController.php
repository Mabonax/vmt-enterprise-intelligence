<?php

declare(strict_types=1);

namespace App\Http\Controllers\Intelligence;

use App\Domains\Intelligence\Agents\Models\AgentApproval;
use App\Domains\Intelligence\Agents\Models\AgentDecision;
use App\Domains\Intelligence\Agents\Models\AgentExecutionMetric;
use App\Domains\Intelligence\Agents\Models\AgentMemory as EnterpriseAgentMemory;
use App\Domains\Intelligence\Agents\Models\AgentMessage;
use App\Domains\Intelligence\Agents\Models\AgentQualityScore;
use App\Domains\Intelligence\Agents\Models\AgentReasoningChain;
use App\Domains\Intelligence\Agents\Models\AgentSession;
use App\Domains\Intelligence\Agents\Models\AgentSummary;
use App\Domains\Intelligence\Agents\Models\AgentTeamMember;
use App\Domains\Intelligence\Agents\Models\AgentWorkflow;
use App\Domains\Intelligence\Agents\Monitoring\AgentMonitoringService;
use App\Domains\Intelligence\Commercial\Models\BillingAccount;
use App\Domains\Intelligence\Commercial\Models\DeploymentRunbook;
use App\Domains\Intelligence\Commercial\Models\IntelligencePackage;
use App\Domains\Intelligence\Commercial\Models\IntelligenceProposal;
use App\Domains\Intelligence\Commercial\Models\IntelligenceSubscription;
use App\Domains\Intelligence\Commercial\Models\IntelligenceTenant;
use App\Domains\Intelligence\Commercial\Models\ReleaseReadinessCheck;
use App\Domains\Intelligence\Commercial\Models\SupportTicket;
use App\Domains\Intelligence\Commercial\Models\TenantProvisioningRequest;
use App\Domains\Intelligence\Commercial\Models\UsageMeter;
use App\Domains\Intelligence\Commercial\Models\UsageOverage;
use App\Domains\Intelligence\Models\Agent;
use App\Domains\Intelligence\Knowledge\Models\KnowledgeCollection;
use App\Domains\Intelligence\Knowledge\Models\KnowledgeDocument;
use App\Domains\Intelligence\Knowledge\Models\KnowledgeEmbedding;
use App\Domains\Intelligence\Knowledge\Models\KnowledgeEvent;
use App\Domains\Intelligence\Knowledge\Models\KnowledgeGraphEdge;
use App\Domains\Intelligence\Knowledge\Models\KnowledgeGraphNode;
use App\Domains\Intelligence\Knowledge\Models\KnowledgeLearningCycle;
use App\Domains\Intelligence\Knowledge\Models\KnowledgeMemory;
use App\Domains\Intelligence\Knowledge\Models\KnowledgeRelationship;
use App\Domains\Intelligence\Knowledge\Models\KnowledgeSearchLog;
use App\Domains\Intelligence\Knowledge\Services\KnowledgeAnalyticsService;
use App\Domains\Intelligence\Knowledge\Services\KnowledgeHealthService;
use App\Domains\Intelligence\Operations\Models\AgentAvailability as OperationsAgentAvailability;
use App\Domains\Intelligence\Operations\Models\AgentCapability as OperationsAgentCapability;
use App\Domains\Intelligence\Operations\Models\AgentCatalog;
use App\Domains\Intelligence\Operations\Models\AgentProvider as OperationsAgentProvider;
use App\Domains\Intelligence\Operations\Models\AgentSkill as OperationsAgentSkill;
use App\Domains\Intelligence\Operations\Models\EnterpriseEvent;
use App\Domains\Intelligence\Operations\Models\EnterpriseKpiSnapshot;
use App\Domains\Intelligence\Operations\Models\EnterpriseMission;
use App\Domains\Intelligence\Operations\Models\MissionApproval as OperationsMissionApproval;
use App\Domains\Intelligence\Operations\Models\MissionCheckpoint;
use App\Domains\Intelligence\Operations\Models\MissionComplianceReview;
use App\Domains\Intelligence\Operations\Models\MissionDecisionRecord;
use App\Domains\Intelligence\Operations\Models\MissionDependency;
use App\Domains\Intelligence\Operations\Models\MissionExecution;
use App\Domains\Intelligence\Operations\Models\MissionLearningCycle;
use App\Domains\Intelligence\Operations\Models\MissionMetric;
use App\Domains\Intelligence\Operations\Models\MissionMilestone;
use App\Domains\Intelligence\Operations\Models\MissionObjective as OperationsMissionObjective;
use App\Domains\Intelligence\Operations\Models\MissionOutcome;
use App\Domains\Intelligence\Operations\Models\MissionPhase;
use App\Domains\Intelligence\Operations\Models\MissionPlanVersion;
use App\Domains\Intelligence\Operations\Models\MissionPolicy;
use App\Domains\Intelligence\Operations\Models\MissionPrediction;
use App\Domains\Intelligence\Operations\Models\MissionRisk;
use App\Domains\Intelligence\Operations\Models\ScenarioSimulation;
use App\Domains\Intelligence\Operations\Services\KpiCalculationService;
use App\Domains\Intelligence\Operations\Services\MissionReportingService;
use App\Domains\Intelligence\Operations\Services\OperationsMonitor;
use App\Domains\Intelligence\Models\AiTool;
use App\Domains\Intelligence\Models\BackgroundTask;
use App\Domains\Intelligence\Models\Conversation;
use App\Domains\Intelligence\Models\ExecutionPlan;
use App\Domains\Intelligence\Models\ExecutionTrace;
use App\Domains\Intelligence\Models\ModelRoutingRule;
use App\Domains\Intelligence\Models\PromptTemplate;
use App\Domains\Intelligence\Models\SemanticMemory;
use App\Domains\Intelligence\Models\ToolExecutionLog;
use App\Domains\Intelligence\Models\VerificationLog;
use App\Domains\Intelligence\Models\WorkflowExecution;
use App\Domains\Intelligence\Services\ToolAnalyticsService;
use App\Domains\Intelligence\Services\ToolMarketplaceService;
use App\Domains\Intelligence\Services\UsageTrackingService;
use App\Domains\Intelligence\Tools\Models\ConnectorHealth;
use App\Domains\Intelligence\Tools\Models\ConnectorRegistration;
use App\Domains\Intelligence\Tools\Models\EnterpriseTool;
use App\Domains\Intelligence\Tools\Models\ExecutionStream;
use App\Domains\Intelligence\Tools\Models\MarketplaceInstallation;
use App\Domains\Intelligence\Tools\Models\MarketplacePackage;
use App\Domains\Intelligence\Tools\Models\SdkExport;
use App\Domains\Intelligence\Tools\Models\ToolCost;
use App\Domains\Intelligence\Tools\Models\ToolCredential;
use App\Domains\Intelligence\Tools\Models\ToolExecution;
use App\Domains\Intelligence\Tools\Models\ToolExecutionGraph;
use App\Domains\Intelligence\Tools\Models\ToolHealth;
use App\Domains\Intelligence\Tools\Models\ToolTestRun;
use App\Domains\Intelligence\Tools\Models\ToolUsage;
use App\Http\Controllers\Controller;
use App\Support\Navigation\VipNavigation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WorkspaceController extends Controller
{
    public function __construct(
        private readonly UsageTrackingService $usageTracking,
        private readonly ToolAnalyticsService $analytics,
        private readonly ToolMarketplaceService $marketplace,
        private readonly KnowledgeAnalyticsService $knowledgeAnalytics,
        private readonly KnowledgeHealthService $knowledgeHealth,
        private readonly AgentMonitoringService $agentMonitoring,
        private readonly OperationsMonitor $operationsMonitor,
        private readonly KpiCalculationService $kpis,
        private readonly MissionReportingService $missionReporting,
    ) {}

    public function __invoke(Request $request): Response
    {
        $slug = (string) $request->route('page', 'dashboard');
        $page = VipNavigation::find($slug);

        abort_if($page === null || $page['group'] !== 'intelligence', 404);

        return Inertia::render('Intelligence/Workspace', [
            'page' => [
                'title' => $page['label'],
                'slug' => $page['slug'],
                'description' => $page['description'],
                'eyebrow' => 'Enterprise intelligence runtime',
                'cards' => $this->cardsFor($slug),
            ],
            'metrics' => $this->metrics(),
            'sections' => $this->sectionsFor($slug),
        ]);
    }

    private function metrics(): array
    {
        $usage = $this->usageTracking->summary();

        return [
            ['label' => 'Conversations', 'value' => (string) Conversation::query()->count(), 'detail' => 'Conversation runtime is scaffolded and persistence-ready.'],
            ['label' => 'Providers', 'value' => (string) count(config('intelligence.discovered.providers', [])), 'detail' => 'Stub providers discovered through the service provider.'],
            ['label' => 'Agents', 'value' => (string) Agent::query()->count(), 'detail' => 'Persisted agents now participate in resolution and execution traces.'],
            ['label' => 'Tools', 'value' => (string) AiTool::query()->count(), 'detail' => 'Discovered tools are catalogued and can be executed through the runtime.'],
            ['label' => 'Enterprise tools', 'value' => class_exists(EnterpriseTool::class) ? (string) EnterpriseTool::query()->count() : '0', 'detail' => 'Connector-backed enterprise tools are versioned, governed, and marketplace-ready.'],
            ['label' => 'Connectors', 'value' => class_exists(ConnectorRegistration::class) ? (string) ConnectorRegistration::query()->count() : '0', 'detail' => 'Connector drivers are discovered and health-tracked independently from tool definitions.'],
            ['label' => 'Knowledge docs', 'value' => class_exists(KnowledgeDocument::class) ? (string) KnowledgeDocument::query()->count() : '0', 'detail' => 'Ingested enterprise documents now feed chunking, embeddings, search, graphing, and memory.'],
            ['label' => 'Memories', 'value' => class_exists(KnowledgeMemory::class) ? (string) KnowledgeMemory::query()->count() : '0', 'detail' => 'Persistent organizational memories are available for retrieval before prompt assembly.' ],
            ['label' => 'Agent sessions', 'value' => class_exists(AgentSession::class) ? (string) AgentSession::query()->count() : '0', 'detail' => 'Multi-agent sessions capture queued, paused, running, and completed enterprise executions.'],
            ['label' => 'Approvals', 'value' => class_exists(AgentApproval::class) ? (string) AgentApproval::query()->count() : '0', 'detail' => 'Execution approvals pause and release agent workflows without bypassing governance.'],
            ['label' => 'Missions', 'value' => class_exists(EnterpriseMission::class) ? (string) EnterpriseMission::query()->count() : '0', 'detail' => 'Executive missions now coordinate planning, execution, supervision, compliance, reporting, and learning across the platform.'],
            ['label' => 'Mission executions', 'value' => class_exists(MissionExecution::class) ? (string) MissionExecution::query()->count() : '0', 'detail' => 'Long-running operations are persisted separately from agent sessions with checkpoints, retries, and recovery state.'],
            ['label' => 'Mission events', 'value' => class_exists(EnterpriseEvent::class) ? (string) EnterpriseEvent::query()->count() : '0', 'detail' => 'Replayable enterprise events now capture mission, approval, policy, compliance, and execution milestones.'],
            ['label' => 'KPI snapshots', 'value' => class_exists(EnterpriseKpiSnapshot::class) ? (string) EnterpriseKpiSnapshot::query()->count() : '0', 'detail' => 'Executive KPI snapshots now track autonomy, productivity, quality, recovery, and enterprise success rates.'],
            ['label' => 'Usage records', 'value' => (string) $usage['interactions'], 'detail' => 'Historical usage data stores provider, model, tokens, duration, and errors.'],
            ['label' => 'Tokens', 'value' => (string) ($usage['input_tokens'] + $usage['output_tokens']), 'detail' => 'Token accounting remains provider-neutral.'],
            ['label' => 'Commercial packages', 'value' => class_exists(IntelligencePackage::class) ? (string) IntelligencePackage::query()->count() : '0', 'detail' => 'Sellable enterprise packaging now wraps the runtime in governed entitlements and pricing rules.'],
            ['label' => 'Commercial tenants', 'value' => class_exists(IntelligenceTenant::class) ? (string) IntelligenceTenant::query()->count() : '0', 'detail' => 'Client organizations can now be provisioned, branded, secured, and deployed through the same workspace.'],
            ['label' => 'Subscriptions', 'value' => class_exists(IntelligenceSubscription::class) ? (string) IntelligenceSubscription::query()->count() : '0', 'detail' => 'Commercial subscriptions bridge packaging, usage quotas, invoices, and tenant ownership.'],
            ['label' => 'Support tickets', 'value' => class_exists(SupportTicket::class) ? (string) SupportTicket::query()->count() : '0', 'detail' => 'Support and SLA operations now persist alongside deployments and commercialization readiness.'],
        ];
    }

    private function cardsFor(string $slug): array
    {
        return match ($slug) {
            'intelligence-dashboard' => [
                ['title' => 'Runtime overview', 'body' => 'Provider contracts, agents, plans, workflows, tools, memory, verification, background execution, and traces are wired into one bounded context.'],
                ['title' => 'Additive posture', 'body' => 'Phase 4 extends the earlier runtime without breaking routes, DTOs, provider neutrality, or the existing Inertia workspace.'],
                ['title' => 'Enterprise readiness', 'body' => 'Dynamic planning, multi-step execution, permission-aware tools, workflow checkpoints, and verification-backed replay now support future ERP agents.'],
            ],
            'intelligence-conversations' => [
                ['title' => 'Conversation manager', 'body' => 'Creation, rename, archive, provider switching, message persistence, and export seams now live behind one service.'],
                ['title' => 'Provider independence', 'body' => 'Conversation records store provider and model choices without coupling the application layer to any SDK.'],
                ['title' => 'Unlimited history', 'body' => 'Messages and attachments persist independently so trimming rules can stay runtime-specific rather than destructive.'],
            ],
            'intelligence-providers' => [
                ['title' => 'Unified contract', 'body' => 'Every provider exposes chat, stream, embeddings, models, health, and token estimation through the same interface.'],
                ['title' => 'Stub-only phase', 'body' => 'OpenAI, Ollama, Anthropic, Gemini, and LM Studio are registered as stubs with zero external calls.'],
                ['title' => 'Discovery-ready', 'body' => 'The runtime binds the configured default provider from discovered provider classes at boot.'],
            ],
            'intelligence-agents' => [
                ['title' => 'Agent runtime', 'body' => 'Agents now persist with visibility, limits, model defaults, allowed tools, and memory controls.'],
                ['title' => 'Execution seam', 'body' => 'Conversation execution can resolve a stored agent and push planning, routing, tools, and verification through one service.'],
                ['title' => 'Permission seams', 'body' => 'Agent access stays explicit through ownership, visibility, and policy authorization.'],
            ],
            'intelligence-tools' => [
                ['title' => 'Registry bridge', 'body' => 'Phase 5 keeps the phase-4 AI tool catalog intact while syncing a richer enterprise tool registry with versions, packages, dependencies, and connector metadata.'],
                ['title' => 'Connector dispatch', 'body' => 'Tool execution can now route through Laravel services, REST, GraphQL, storage, queues, Redis, OpenAPI imports, and future MCP registrations.'],
                ['title' => 'Governed execution', 'body' => 'Every enterprise execution passes sandbox limits, authorization, usage tracking, health scoring, replay payload capture, and streaming checkpoints.'],
            ],
            'intelligence-marketplace' => [
                ['title' => 'Package lifecycle', 'body' => 'Discovered tools can be published into marketplace packages and installed back into the runtime with additive metadata.'],
                ['title' => 'Version posture', 'body' => 'Package and tool versions remain separate so the runtime can resolve compatible variants without mutating prior records.'],
                ['title' => 'Enterprise governance', 'body' => 'Installations, dependencies, publishers, and health all remain visible from one workspace lane.'],
            ],
            'intelligence-connectors' => [
                ['title' => 'Connector registry', 'body' => 'Connectors are discovered at boot and synced into a first-class registration table with health snapshots.'],
                ['title' => 'Provider neutrality', 'body' => 'The runtime treats Laravel, REST, OpenAPI, Redis, queues, and future MCP servers as interchangeable connector drivers.'],
                ['title' => 'Cluster-safe posture', 'body' => 'Connector metadata is persisted so future clustered nodes can reconcile health and capabilities without hardcoded state.'],
            ],
            'intelligence-credentials' => [
                ['title' => 'Secret vault', 'body' => 'Credentials are encrypted at rest, rotation-ready, and separated from prompt payloads.'],
                ['title' => 'Least exposure', 'body' => 'Tools reference vault records through metadata rather than surfacing raw secrets inside prompts or chat payloads.'],
                ['title' => 'Audit readiness', 'body' => 'Credential status, expiry, and rotation timestamps are visible in the workspace.'],
            ],
            'intelligence-analytics' => [
                ['title' => 'Usage analytics', 'body' => 'Most-used, slowest, and most-expensive tools are aggregated from execution, usage, and cost records.'],
                ['title' => 'Operational signals', 'body' => 'Health, runtime, and cost metrics stay decoupled from provider billing semantics.'],
                ['title' => 'Executive visibility', 'body' => 'The analytics feed is shaped for dashboard surfaces without changing runtime contracts.'],
            ],
            'intelligence-health' => [
                ['title' => 'Health scoring', 'body' => 'Tool and connector health snapshots capture availability, latency, errors, success rate, and last failure markers.'],
                ['title' => 'Incident posture', 'body' => 'Health state is stored per entity so dashboards and alerting can query stable records later.'],
                ['title' => 'Execution feedback', 'body' => 'Every connector-backed execution updates health and usage metrics immediately.'],
            ],
            'intelligence-execution-graph' => [
                ['title' => 'Execution tree', 'body' => 'Tool runs persist node records that can be replayed, debugged, and linked back to traces.'],
                ['title' => 'Replay context', 'body' => 'Each node stores input, version, authorization posture, and replay payload.'],
                ['title' => 'Streaming trail', 'body' => 'Execution streams record ordered status messages for long-running tools.'],
            ],
            'intelligence-execution-history' => [
                ['title' => 'Unified history', 'body' => 'Phase 5 supplements execution traces with enterprise tool executions and execution graph nodes.'],
                ['title' => 'Dual-runtime compatibility', 'body' => 'Legacy phase-4 tool logs remain untouched while the enterprise registry captures richer connector data.'],
                ['title' => 'Debugging focus', 'body' => 'Execution history keeps both human-readable messages and structured payloads.'],
            ],
            'intelligence-testing' => [
                ['title' => 'Connector validation', 'body' => 'Tool test runs now have a dedicated persistence surface for regression, replay, contract, and permission testing.'],
                ['title' => 'Replay confidence', 'body' => 'Stored replay payloads make later validation deterministic enough for enterprise debugging workflows.'],
                ['title' => 'Additive coverage', 'body' => 'Phase 5 tests extend the current suite without replacing the earlier runtime assertions.'],
            ],
            'intelligence-sdk' => [
                ['title' => 'SDK export lane', 'body' => 'SDK export records track language, status, and output path for future package generation.'],
                ['title' => 'Contract-first', 'body' => 'Exports are intended to derive from tool manifests rather than bespoke handwritten wrappers.'],
                ['title' => 'Marketplace alignment', 'body' => 'Generated SDKs can follow the same package/version posture as marketplace tools.'],
            ],
            'intelligence-costs' => [
                ['title' => 'Cost ledger', 'body' => 'Execution costs now store API, token, storage, bandwidth, and credit signals independently.'],
                ['title' => 'On-prem flexibility', 'body' => 'Zero-cost internal tools still participate in the same analytics flow without forced pricing assumptions.'],
                ['title' => 'Budget observability', 'body' => 'Cost records are grouped by tool and execution trace to support later monthly rollups.'],
            ],
            'intelligence-knowledge-dashboard' => [
                ['title' => 'Knowledge runtime', 'body' => 'Phase 6 adds enterprise documents, memories, embeddings, graph nodes, learning cycles, and search logs without replacing earlier runtime services.'],
                ['title' => 'RAG posture', 'body' => 'Context assembly can now retrieve relevant memories and knowledge records before prompt assembly, while preserving earlier memory behaviour.'],
                ['title' => 'Queued ingestion', 'body' => 'Chunking, embeddings, entity extraction, relationship discovery, verification, and maintenance are scheduled as background work.'],
            ],
            'intelligence-enterprise-search' => [
                ['title' => 'Hybrid search', 'body' => 'Search combines keyword overlap, embedding similarity, and graph density to retrieve documents and memories.'],
                ['title' => 'Workspace-aware retrieval', 'body' => 'Search logs and retrieval settings remain workspace-sensitive so future request-type overrides stay additive.'],
                ['title' => 'Operational traceability', 'body' => 'Each search request records latency, result counts, and filters for analytics.' ],
            ],
            'intelligence-knowledge-graph' => [
                ['title' => 'Graph explorer', 'body' => 'Knowledge graph nodes and edges model documents, entities, tools, connectors, and future enterprise relationships.'],
                ['title' => 'Traversal-ready', 'body' => 'Graph traversal is available through a dedicated service and API endpoint for future graph-native workflows.'],
                ['title' => 'Confidence-aware', 'body' => 'Edges persist relationship confidence so downstream ranking can stay data-driven.' ],
            ],
            'intelligence-memory-explorer' => [
                ['title' => 'Persistent memory', 'body' => 'Knowledge memories capture type, importance, confidence, classification, citations, relationships, and lifecycle metadata.'],
                ['title' => 'Learning loop', 'body' => 'Usage counts and last-used timestamps provide a stable signal for consolidation and ranking.'],
                ['title' => 'Tenant-safe', 'body' => 'Memory records preserve tenant and visibility boundaries for future policy tightening.' ],
            ],
            'intelligence-document-library' => [
                ['title' => 'Document intelligence', 'body' => 'Documents hold extracted content, summaries, chunking outputs, embeddings, quality scores, and source metadata.'],
                ['title' => 'Version-safe ingestion', 'body' => 'Documents track versions and checksums so future re-ingest paths can remain additive.'],
                ['title' => 'Source-backed', 'body' => 'Every document can point back to a knowledge source for provenance and audit.' ],
            ],
            'intelligence-collections' => [
                ['title' => 'Collection boundaries', 'body' => 'Collections provide workspace-level organization for enterprise knowledge stores.'],
                ['title' => 'Retrieval targeting', 'body' => 'Collections are the additive seam for future request-type and workspace-specific retrieval rules.'],
                ['title' => 'Governance ready', 'body' => 'Collection metadata can carry ownership and policy context without changing the retrieval interfaces.' ],
            ],
            'intelligence-embeddings' => [
                ['title' => 'Embedding abstraction', 'body' => 'Embeddings are provider-neutral records so OpenAI, Ollama, Sentence Transformers, or future providers can be swapped without leaking into business logic.'],
                ['title' => 'Chunk-aligned', 'body' => 'Embeddings attach to chunks instead of entire documents so hybrid retrieval can stay precise.'],
                ['title' => 'Refreshable', 'body' => 'Scheduled maintenance can regenerate embeddings without disturbing documents or memories.' ],
            ],
            'intelligence-relationships' => [
                ['title' => 'Discovered relationships', 'body' => 'Entity and document relationships are persisted independently from graph edges so both search and graph use cases stay supported.'],
                ['title' => 'Confidence scoring', 'body' => 'Relationship confidence is stored and ready for future ranking models.'],
                ['title' => 'Enterprise coverage', 'body' => 'The relationship model is broad enough to absorb users, workflows, customers, tools, and connectors in later phases.' ],
            ],
            'intelligence-learning' => [
                ['title' => 'Learning cycles', 'body' => 'Every runtime execution can now contribute planner, verification, latency, and tool-usage signals into learning cycles.'],
                ['title' => 'Scheduled refinement', 'body' => 'Background learning jobs provide a stable seam for future ranking and retrieval optimization.'],
                ['title' => 'Feedback ready', 'body' => 'Learning records are designed to absorb user feedback without changing the runtime interface.' ],
            ],
            'intelligence-knowledge-timeline' => [
                ['title' => 'Timeline events', 'body' => 'Knowledge events capture ingestion, health, consolidation, and reindex activity in chronological order.'],
                ['title' => 'Operational visibility', 'body' => 'The timeline forms the basis for future heat maps, growth charts, and maintenance auditing.'],
                ['title' => 'Queue observability', 'body' => 'Background jobs can append event records without coupling to frontend rendering details.' ],
            ],
            'intelligence-knowledge-analytics' => [
                ['title' => 'Knowledge growth', 'body' => 'Analytics summarize documents, memories, embeddings, searches, and learning cycles.'],
                ['title' => 'Search insights', 'body' => 'Search latency and volume are stored for operational analysis and retrieval tuning.'],
                ['title' => 'Reuse signals', 'body' => 'Memory and learning records create a measurable base for future hit-rate and reuse reporting.' ],
            ],
            'intelligence-knowledge-health' => [
                ['title' => 'Knowledge health', 'body' => 'Freshness, authority, confidence, coverage, density, citation quality, and semantic richness are exposed as first-class metrics.'],
                ['title' => 'Operational checks', 'body' => 'Health calculations are schedulable so maintenance remains asynchronous and production-safe.'],
                ['title' => 'RAG confidence', 'body' => 'Health metrics provide a practical readiness signal for retrieval-backed prompts.' ],
            ],
            'intelligence-enterprise-agents' => [
                ['title' => 'Multi-agent coordinator', 'body' => 'Phase 7 introduces a session-based coordinator that plans execution, builds teams, shares memory, delegates work, and persists verification outcomes.'],
                ['title' => 'Specialist roles', 'body' => 'Built-in enterprise roles define prompt posture, capabilities, tool preferences, reasoning style, risk tolerance, and memory scope.'],
                ['title' => 'Long-running posture', 'body' => 'Sessions are queue-aware, checkpointed, pausable, resumable, approval-safe, and replayable without replacing the earlier runtime.' ],
            ],
            'intelligence-agent-teams' => [
                ['title' => 'Role composition', 'body' => 'Sessions assemble role-specialized teams for planning, research, knowledge, implementation, review, and executive synthesis.'],
                ['title' => 'Delegation traceability', 'body' => 'Task assignments, collaborations, and messages make every handover auditable.'],
                ['title' => 'Additive identity model', 'body' => 'Existing agents remain the root entities while phase-7 profiles, capabilities, and team members extend them.' ],
            ],
            'intelligence-executions' => [
                ['title' => 'Execution sessions', 'body' => 'Every run has a durable session key, objective, workflow, queue state, and result payload.'],
                ['title' => 'Queue discipline', 'body' => 'Queued execution remains compatible with workers, resumable checkpoints, and future scheduler automation.'],
                ['title' => 'Human-in-the-loop', 'body' => 'Approval-required sessions pause cleanly and resume from persisted state rather than ad hoc memory.' ],
            ],
            'intelligence-reasoning' => [
                ['title' => 'Reasoning metadata', 'body' => 'Goal, plan, actions, evidence, tool usage, confidence, and final decision records are stored without exposing private chain-of-thought to end users.'],
                ['title' => 'Thought snapshots', 'body' => 'Each step emits a constrained snapshot summarizing what changed and why.'],
                ['title' => 'Graph-ready', 'body' => 'Reasoning and task topology are replayable as a structured graph for monitoring and audits.' ],
            ],
            'intelligence-approvals' => [
                ['title' => 'Approval gates', 'body' => 'Manager, executive, human, or administrator approvals can halt execution before sensitive work continues.'],
                ['title' => 'Decision trace', 'body' => 'Requested role, requester, approver, notes, and decision timestamps persist independently from session state.'],
                ['title' => 'Governed resume', 'body' => 'Approved sessions return to the queue rather than bypassing the runtime.' ],
            ],
            'intelligence-communication' => [
                ['title' => 'Agent messaging', 'body' => 'Requests, responses, handovers, feedback, and context references travel through persisted conversations.'],
                ['title' => 'Evidence-linked', 'body' => 'Messages can carry knowledge references and tool outputs without coupling the frontend to raw execution internals.'],
                ['title' => 'Collaboration timeline', 'body' => 'Communication records enrich replay and execution narratives.' ],
            ],
            'intelligence-shared-memory' => [
                ['title' => 'Shared context', 'body' => 'Agents share documents, temporary context, tool outputs, and execution findings through explicit memory records.'],
                ['title' => 'Phase 6 integration', 'body' => 'Completed sessions contribute to the knowledge memory and learning services instead of building a second memory system.'],
                ['title' => 'Scope control', 'body' => 'Memory scope is tracked per role and per session for future policy tightening.' ],
            ],
            'intelligence-performance' => [
                ['title' => 'Execution metrics', 'body' => 'Latency, token, utilization, and cost metrics are tracked per multi-agent session.'],
                ['title' => 'Queue health', 'body' => 'The runtime separates queue state from workflow state so operational issues remain observable.'],
                ['title' => 'On-prem posture', 'body' => 'Metrics remain useful even when some tools or providers are local and zero-cost.' ],
            ],
            'intelligence-quality' => [
                ['title' => 'Reviewer posture', 'body' => 'Quality scores capture completeness, evidence, policy, and verification dimensions.'],
                ['title' => 'Hallucination resistance', 'body' => 'Verification coverage tracks missing tools and missing evidence before a session is marked complete.'],
                ['title' => 'Operational quality', 'body' => 'Quality records stay decoupled from the raw reasoning chain so dashboards can aggregate them safely.' ],
            ],
            'intelligence-decision-records' => [
                ['title' => 'Decision records', 'body' => 'Phase 7 stores why a path was chosen, which alternatives were considered, and which risks remain.'],
                ['title' => 'Evidence bundles', 'body' => 'Tool evidence and knowledge evidence are attached independently for audit readiness.'],
                ['title' => 'Executive synthesis', 'body' => 'Decision records feed summaries and reporting without flattening the underlying runtime data.' ],
            ],
            'intelligence-monitoring' => [
                ['title' => 'Operational monitoring', 'body' => 'Running sessions, queued work, pending approvals, quality, and latency are summarized in one workspace lane.'],
                ['title' => 'Agent health', 'body' => 'Monitoring services expose reusable metrics for command center dashboards and future alerts.'],
                ['title' => 'Replay posture', 'body' => 'Monitoring and replay are complementary: one tracks current health, the other reconstructs what happened.' ],
            ],
            'intelligence-executive-dashboard' => [
                ['title' => 'Executive orchestration', 'body' => 'Phase 8 adds a distinct operations layer above agents that decomposes enterprise objectives into initiatives, programmes, projects, work packages, and executable tasks.'],
                ['title' => 'Autonomous supervision', 'body' => 'Mission planning, execution supervision, approvals, compliance, prediction, simulation, KPI reporting, and learning are now persisted as first-class operations records.'],
                ['title' => 'Strictly additive', 'body' => 'The existing planning, workflow, verification, knowledge, tool, connector, memory, and multi-agent seams remain intact and are reused rather than replaced.' ],
            ],
            'intelligence-enterprise-missions' => [
                ['title' => 'Mission governance', 'body' => 'Missions persist priorities, ownership, budgets, deadlines, health, risks, dependencies, completion, and estimated completion across the full lifecycle.'],
                ['title' => 'Executive control', 'body' => 'Mission records unify objectives, phases, executions, outcomes, approvals, and reports into one operational contract.'],
                ['title' => 'Backward compatibility', 'body' => 'Agents remain workers while the operations context acts as the executive orchestration layer.' ],
            ],
            'intelligence-mission-planner' => [
                ['title' => 'Versioned planning', 'body' => 'Mission plans now estimate complexity, duration, token cost, financial cost, tool requirements, knowledge requirements, skills, confidence, risk, compliance, and alternatives.'],
                ['title' => 'Critical path', 'body' => 'Planning explicitly models dependency chains from initiatives to executable tasks for supervision and recovery.'],
                ['title' => 'Comparison-ready', 'body' => 'Alternative plans preserve different optimization strategies without mutating earlier plan versions.' ],
            ],
            'intelligence-agent-marketplace' => [
                ['title' => 'Dynamic team assembly', 'body' => 'Teams are ranked by historical success, skills, trust, workload, provider support, latency, cost, tool ownership, language, and permissions.'],
                ['title' => 'Marketplace coverage', 'body' => 'Operations agent catalog records capabilities, versions, skills, availability, health, and compatibility in one search lane.'],
                ['title' => 'Rebalancing seam', 'body' => 'Assignments remain additive so future runtime rebalancing can happen without breaking existing agent contracts.' ],
            ],
            'intelligence-mission-timeline',
            'intelligence-operations-centre',
            'intelligence-execution-centre',
            'intelligence-simulation-studio',
            'intelligence-predictions',
            'intelligence-operations-compliance',
            'intelligence-operations-policies',
            'intelligence-operations-approvals',
            'intelligence-enterprise-monitoring',
            'intelligence-operations-decision-records',
            'intelligence-enterprise-kpis',
            'intelligence-autonomy-analytics' => [
                ['title' => 'Operations control plane', 'body' => 'Executive operations pages expose mission state, monitoring, simulations, predictions, approvals, decisions, and KPIs through the existing VIP workspace.'],
                ['title' => 'Replayable state', 'body' => 'Events, checkpoints, predictions, approvals, simulations, and policy evaluations are persisted as replayable operational records.'],
                ['title' => 'Long-running posture', 'body' => 'The platform now supports multi-hour and multi-day autonomous work with supervision, recovery, governance, and continuous improvement built in.' ],
            ],
            'intelligence-commercial-packages' => [
                ['title' => 'Enterprise product packaging', 'body' => 'Phase 9 turns the runtime into a sellable catalog of package tiers, entitlements, limits, modules, pricing rules, and upgrade paths.'],
                ['title' => 'Provider-neutral commercialization', 'body' => 'Commercial packaging constrains usage, support, and deployment posture without breaking the existing runtime, agent, knowledge, or operations domains.'],
                ['title' => 'Controlled expansion', 'body' => 'Limits and entitlements are additive policy overlays rather than destructive runtime switches.' ],
            ],
            'intelligence-commercial-tenants' => [
                ['title' => 'Client portfolio', 'body' => 'Enterprise tenants can now be tracked with deployment mode, branding, security, residency, and workspace readiness in one bounded context.'],
                ['title' => 'Operational provisioning', 'body' => 'The same VIP workspace now supports VMT internal, municipality, healthcare, and education demo tenants.'],
                ['title' => 'Deployment posture', 'body' => 'Shared SaaS, dedicated SaaS, on-premise, private cloud, and sovereign modes are captured per tenant.' ],
            ],
            'intelligence-commercial-provisioning' => [
                ['title' => 'Provisioning workflow', 'body' => 'Draft, submitted, reviewed, approved, provisioning, active, suspended, and retired states are now persisted for enterprise onboarding.'],
                ['title' => 'Checklist tracking', 'body' => 'Provisioning requests carry checklist tasks, workspace preparation, security configuration, and deployment planning.'],
                ['title' => 'Non-breaking rollout', 'body' => 'Tenant activation extends the platform without replacing earlier intelligence runtime workflows.' ],
            ],
            'intelligence-commercial-subscriptions' => [
                ['title' => 'Commercial subscriptions', 'body' => 'Package-aligned subscriptions now connect billing accounts, invoices, quotas, and tenant usage ownership.'],
                ['title' => 'Meter readiness', 'body' => 'Usage meters are seeded for agent executions, tokens, storage, and connector calls with non-destructive enforcement.'],
                ['title' => 'Renewal posture', 'body' => 'Subscriptions stay deployment-safe and billing-safe without introducing a real payment gateway yet.' ],
            ],
            'intelligence-commercial-usage' => [
                ['title' => 'Usage metering', 'body' => 'Agent runs, token consumption, knowledge growth, storage, and connector calls now feed a commercial ledger.'],
                ['title' => 'Quota enforcement', 'body' => 'Warnings and overage flags are recorded before any hard block, and hard limits only apply when package policy requires them.'],
                ['title' => 'Commercial observability', 'body' => 'Usage signals now complement runtime analytics instead of replacing them.' ],
            ],
            'intelligence-commercial-billing' => [
                ['title' => 'Billing foundation', 'body' => 'Billing accounts, contacts, payment instructions, draft invoices, and readiness checks now persist in-app.'],
                ['title' => 'Invoice payloads', 'body' => 'Structured invoice data is generated without coupling the platform to a payment gateway.'],
                ['title' => 'Operational billing', 'body' => 'Commercial readiness can now highlight missing contacts and account setup before go-live.' ],
            ],
            'intelligence-commercial-proposals' => [
                ['title' => 'Proposal pipeline', 'body' => 'Commercial proposals now convert package and deployment data into structured lines, assumptions, risks, approvals, and handover payloads.'],
                ['title' => 'Stage governance', 'body' => 'Draft, internal review, approved, sent, accepted, rejected, and converted states are captured explicitly.'],
                ['title' => 'Document-safe posture', 'body' => 'Payload preparation is structured for later PDF generation without introducing unsafe document flows now.' ],
            ],
            'intelligence-commercial-deployments' => [
                ['title' => 'Deployment runbooks', 'body' => 'Runbooks, ordered steps, evidence records, and maintenance planning now extend the Intelligence platform into deployment operations.'],
                ['title' => 'Support handover', 'body' => 'Commercial handover and deployment readiness are linked to support planning and tenant activation.'],
                ['title' => 'Launch control', 'body' => 'Deployment sequencing is additive and auditable through the same workspace.' ],
            ],
            'intelligence-commercial-support' => [
                ['title' => 'Support operations', 'body' => 'Support plans, SLA thresholds, escalations, and ticket stages now persist for active commercial tenants.'],
                ['title' => 'Workflow tracking', 'body' => 'New, triaged, assigned, in progress, waiting on client, resolved, and closed states are tracked without a separate support shell.'],
                ['title' => 'Commercial retention', 'body' => 'Support posture is visible next to subscriptions, deployment, and readiness.' ],
            ],
            'intelligence-commercial-readiness' => [
                ['title' => 'Release readiness', 'body' => 'Workspace, security, branding, deployment profile, and runbook checks now produce a commercial launch score.'],
                ['title' => 'Go-live confidence', 'body' => 'Readiness checks are stored as auditable records before deployment and handover.'],
                ['title' => 'Executive view', 'body' => 'Commercial KPIs can now sit beside runtime and operations posture inside the same VIP workspace.' ],
            ],
            'intelligence-commercial-settings' => [
                ['title' => 'Commercial defaults', 'body' => 'Packaging, metering, billing, support, and deployment data can now be reviewed through one commercial settings lane.'],
                ['title' => 'Safe extension points', 'body' => 'This page keeps the commercialization surface additive and ready for future gateway, taxation, and PDF integrations.'],
                ['title' => 'Tenant-safe evolution', 'body' => 'Settings remain bounded to commercial operations without disturbing runtime intelligence services.' ],
            ],
            'intelligence-agent-settings' => [
                ['title' => 'Coordinator defaults', 'body' => 'Execution mode and approval-role defaults now live in the intelligence config alongside earlier runtime settings.'],
                ['title' => 'Phase compatibility', 'body' => 'Multi-agent defaults extend the current runtime instead of replacing prior provider, tool, workflow, or knowledge settings.'],
                ['title' => 'Production-safe posture', 'body' => 'The coordinator stays queue-first and approval-aware by default.' ],
            ],
            'intelligence-usage' => [
                ['title' => 'Historical tracking', 'body' => 'Provider, model, tokens, duration, success, error, and nullable cost are persisted per interaction.'],
                ['title' => 'Dashboard feed', 'body' => 'UsageTrackingService aggregates the metrics used by the intelligence dashboard placeholders.'],
                ['title' => 'Cost-safe extension', 'body' => 'Nullable costs allow local or on-prem models to participate without forced billing semantics.'],
            ],
            'intelligence-settings' => [
                ['title' => 'Runtime config', 'body' => 'Default agent, routing, memory, verification, approvals, workflows, and streaming flags now live in config/intelligence.php.'],
                ['title' => 'Environment-safe', 'body' => 'No provider secrets are required in this phase, keeping the runtime deployable without vendor lock-in.'],
                ['title' => 'Deployment alignment', 'body' => 'Configuration is container-safe and compatible with the existing Docker Compose posture.'],
            ],
            default => [
                ['title' => 'Scaffold ready', 'body' => 'This intelligence workspace is additive and ready for later phases.'],
                ['title' => 'Contract first', 'body' => 'Runtime abstractions land before external integrations.'],
                ['title' => 'Replaceable by design', 'body' => 'Providers, agents, tools, and memory strategies remain independently swappable.'],
            ],
        };
    }

    private function sectionsFor(string $slug): array
    {
        return match ($slug) {
            'intelligence-agents' => [
                ['title' => 'Agents', 'items' => Agent::query()->latest()->get(['name', 'slug', 'status', 'visibility', 'default_provider', 'default_model'])->toArray()],
            ],
            'intelligence-tools' => [
                ['title' => 'Tool Explorer', 'items' => AiTool::query()->orderBy('name')->get(['name', 'slug', 'category', 'status', 'permission_key'])->toArray()],
                ['title' => 'Enterprise Registry', 'items' => class_exists(EnterpriseTool::class) ? EnterpriseTool::query()->orderBy('name')->limit(20)->get(['name', 'slug', 'connector_type', 'version', 'status'])->toArray() : []],
                ['title' => 'Tool Logs', 'items' => ToolExecutionLog::query()->latest()->limit(10)->get(['tool_name', 'status', 'duration_ms', 'created_at'])->toArray()],
            ],
            'intelligence-marketplace' => [
                ['title' => 'Available Packages', 'items' => $this->marketplace->availablePackages()],
                ['title' => 'Installations', 'items' => MarketplaceInstallation::query()->latest()->limit(10)->get(['status', 'installed_version', 'created_at'])->toArray()],
            ],
            'intelligence-connectors' => [
                ['title' => 'Connector Registry', 'items' => ConnectorRegistration::query()->orderBy('name')->get(['name', 'slug', 'driver', 'status'])->toArray()],
                ['title' => 'Connector Health', 'items' => ConnectorHealth::query()->latest()->limit(10)->get(['status', 'latency_ms', 'last_checked_at'])->toArray()],
            ],
            'intelligence-credentials' => [
                ['title' => 'Credential Vault', 'items' => ToolCredential::query()->latest()->limit(10)->get(['name', 'slug', 'type', 'status', 'expires_at'])->toArray()],
            ],
            'intelligence-analytics' => [
                ['title' => 'Analytics Summary', 'items' => $this->analytics->summary()['usage']],
                ['title' => 'Cost Summary', 'items' => $this->analytics->summary()['costs']],
            ],
            'intelligence-health' => [
                ['title' => 'Tool Health', 'items' => ToolHealth::query()->latest()->limit(10)->get(['availability', 'latency_ms', 'success_rate', 'health_score', 'last_execution_at'])->toArray()],
                ['title' => 'Connector Health', 'items' => ConnectorHealth::query()->latest()->limit(10)->get(['status', 'latency_ms', 'last_checked_at'])->toArray()],
            ],
            'intelligence-settings' => [
                ['title' => 'Model Routing', 'items' => ModelRoutingRule::query()->orderBy('priority')->get(['provider', 'model', 'capability', 'priority', 'enabled'])->toArray()],
            ],
            'intelligence-conversations' => [
                ['title' => 'Execution Plans', 'items' => ExecutionPlan::query()->latest()->limit(10)->get(['objective', 'status', 'completion_state', 'estimated_complexity', 'created_at'])->toArray()],
                ['title' => 'Execution Traces', 'items' => ExecutionTrace::query()->latest()->limit(10)->get(['provider', 'model', 'status', 'completion_reason', 'iterations', 'created_at'])->toArray()],
            ],
            'intelligence-dashboard' => [
                ['title' => 'Prompt Registry', 'items' => PromptTemplate::query()->latest()->limit(10)->get(['name', 'slug', 'version', 'status', 'category'])->toArray()],
                ['title' => 'Memory Explorer', 'items' => SemanticMemory::query()->latest()->limit(10)->get(['subject_type', 'memory_type', 'visibility', 'confidence_score', 'created_at'])->toArray()],
                ['title' => 'Workflow Monitor', 'items' => WorkflowExecution::query()->latest()->limit(10)->get(['name', 'status', 'created_at'])->toArray()],
                ['title' => 'Verification Logs', 'items' => VerificationLog::query()->latest()->limit(10)->get(['status', 'confidence_score', 'created_at'])->toArray()],
                ['title' => 'Background Jobs', 'items' => BackgroundTask::query()->latest()->limit(10)->get(['type', 'status', 'progress', 'created_at'])->toArray()],
            ],
            'intelligence-planner' => [
                ['title' => 'Planner', 'items' => ExecutionPlan::query()->latest()->limit(10)->get(['objective', 'status', 'completion_state', 'estimated_complexity', 'max_iterations'])->toArray()],
            ],
            'intelligence-memory' => [
                ['title' => 'Memory Explorer', 'items' => SemanticMemory::query()->latest()->limit(10)->get(['subject_type', 'memory_type', 'visibility', 'content'])->toArray()],
            ],
            'intelligence-prompts' => [
                ['title' => 'Prompt Registry', 'items' => PromptTemplate::query()->latest()->limit(10)->get(['name', 'slug', 'version', 'status', 'category'])->toArray()],
            ],
            'intelligence-routing' => [
                ['title' => 'Model Routing', 'items' => ModelRoutingRule::query()->orderBy('priority')->get(['provider', 'model', 'capability', 'priority', 'enabled'])->toArray()],
            ],
            'intelligence-traces' => [
                ['title' => 'Execution Traces', 'items' => ExecutionTrace::query()->latest()->limit(10)->get(['provider', 'model', 'status', 'completion_reason', 'iterations'])->toArray()],
            ],
            'intelligence-execution-graph' => [
                ['title' => 'Execution Nodes', 'items' => ToolExecutionGraph::query()->latest()->limit(10)->get(['node_key', 'depth', 'created_at'])->toArray()],
                ['title' => 'Execution Streams', 'items' => ExecutionStream::query()->latest()->limit(10)->get(['status', 'message', 'sequence', 'created_at'])->toArray()],
            ],
            'intelligence-execution-history' => [
                ['title' => 'Enterprise Executions', 'items' => ToolExecution::query()->latest()->limit(10)->get(['status', 'connector_type', 'version', 'duration_ms', 'created_at'])->toArray()],
                ['title' => 'Legacy Tool Logs', 'items' => ToolExecutionLog::query()->latest()->limit(10)->get(['tool_name', 'status', 'duration_ms', 'created_at'])->toArray()],
            ],
            'intelligence-workflows' => [
                ['title' => 'Workflow Monitor', 'items' => WorkflowExecution::query()->latest()->limit(10)->get(['name', 'status', 'created_at'])->toArray()],
            ],
            'intelligence-testing' => [
                ['title' => 'Tool Test Runs', 'items' => ToolTestRun::query()->latest()->limit(10)->get(['test_type', 'status', 'created_at'])->toArray()],
            ],
            'intelligence-sdk' => [
                ['title' => 'SDK Exports', 'items' => SdkExport::query()->latest()->limit(10)->get(['language', 'status', 'path', 'created_at'])->toArray()],
            ],
            'intelligence-costs' => [
                ['title' => 'Tool Costs', 'items' => ToolCost::query()->latest()->limit(10)->get(['estimated_cost', 'credits_consumed', 'created_at'])->toArray()],
                ['title' => 'Tool Usage', 'items' => ToolUsage::query()->latest()->limit(10)->get(['success', 'duration_ms', 'tokens', 'created_at'])->toArray()],
            ],
            'intelligence-knowledge-dashboard' => [
                ['title' => 'Knowledge Metrics', 'items' => [[
                    'documents' => $this->knowledgeAnalytics->summary()['documents'],
                    'memories' => $this->knowledgeAnalytics->summary()['memories'],
                    'embeddings' => $this->knowledgeAnalytics->summary()['embeddings'],
                    'searches' => $this->knowledgeAnalytics->summary()['searches'],
                ]]],
                ['title' => 'Recent Documents', 'items' => KnowledgeDocument::query()->latest()->limit(10)->get(['title', 'status', 'mime_type', 'quality_score', 'created_at'])->toArray()],
            ],
            'intelligence-enterprise-search' => [
                ['title' => 'Search Logs', 'items' => KnowledgeSearchLog::query()->latest()->limit(10)->get(['query', 'workspace', 'latency_ms', 'result_count', 'created_at'])->toArray()],
            ],
            'intelligence-knowledge-graph' => [
                ['title' => 'Graph Nodes', 'items' => KnowledgeGraphNode::query()->latest()->limit(15)->get(['node_type', 'label', 'created_at'])->toArray()],
                ['title' => 'Graph Edges', 'items' => KnowledgeGraphEdge::query()->latest()->limit(15)->get(['relationship', 'confidence_score', 'created_at'])->toArray()],
            ],
            'intelligence-memory-explorer' => [
                ['title' => 'Knowledge Memories', 'items' => KnowledgeMemory::query()->latest()->limit(15)->get(['title', 'memory_type', 'importance', 'confidence', 'usage_count'])->toArray()],
            ],
            'intelligence-document-library' => [
                ['title' => 'Documents', 'items' => KnowledgeDocument::query()->latest()->limit(15)->get(['title', 'source_type', 'status', 'mime_type', 'quality_score'])->toArray()],
            ],
            'intelligence-collections' => [
                ['title' => 'Collections', 'items' => KnowledgeCollection::query()->latest()->limit(15)->get(['name', 'workspace', 'slug', 'created_at'])->toArray()],
            ],
            'intelligence-embeddings' => [
                ['title' => 'Embeddings', 'items' => KnowledgeEmbedding::query()->latest()->limit(15)->get(['provider', 'model', 'status', 'dimensions', 'created_at'])->toArray()],
            ],
            'intelligence-relationships' => [
                ['title' => 'Relationships', 'items' => KnowledgeRelationship::query()->latest()->limit(15)->get(['source_type', 'target_type', 'relationship', 'confidence_score'])->toArray()],
            ],
            'intelligence-learning' => [
                ['title' => 'Learning Cycles', 'items' => KnowledgeLearningCycle::query()->latest()->limit(15)->get(['workspace', 'status', 'outcome', 'confidence', 'latency_ms'])->toArray()],
            ],
            'intelligence-knowledge-timeline' => [
                ['title' => 'Knowledge Events', 'items' => KnowledgeEvent::query()->latest()->limit(15)->get(['event_type', 'subject_type', 'subject_id', 'created_at'])->toArray()],
            ],
            'intelligence-knowledge-analytics' => [
                ['title' => 'Analytics Summary', 'items' => [array_map(fn ($value) => is_scalar($value) ? $value : json_encode($value), $this->knowledgeAnalytics->summary())]],
            ],
            'intelligence-knowledge-health' => [
                ['title' => 'Health Summary', 'items' => [array_map(fn ($value) => is_scalar($value) ? $value : json_encode($value), $this->knowledgeHealth->summary())]],
            ],
            'intelligence-enterprise-agents' => [
                ['title' => 'Enterprise Agents', 'items' => Agent::query()->whereNotNull('agent_role_key')->latest()->limit(15)->get(['name', 'agent_role_key', 'reasoning_style', 'risk_tolerance', 'delegation_enabled', 'approval_required'])->toArray()],
            ],
            'intelligence-agent-teams' => [
                ['title' => 'Team Members', 'items' => AgentTeamMember::query()->latest()->limit(20)->get(['member_name', 'member_type', 'status', 'order_column', 'created_at'])->toArray()],
            ],
            'intelligence-executions' => [
                ['title' => 'Agent Sessions', 'items' => AgentSession::query()->latest()->limit(20)->get(['title', 'status', 'execution_mode', 'approval_role', 'started_at', 'completed_at'])->toArray()],
                ['title' => 'Workflows', 'items' => AgentWorkflow::query()->latest()->limit(20)->get(['name', 'status', 'execution_mode', 'created_at'])->toArray()],
            ],
            'intelligence-reasoning' => [
                ['title' => 'Reasoning Chains', 'items' => AgentReasoningChain::query()->latest()->limit(20)->get(['goal', 'confidence', 'decision', 'created_at'])->toArray()],
            ],
            'intelligence-approvals' => [
                ['title' => 'Approvals', 'items' => AgentApproval::query()->latest()->limit(20)->get(['approval_type', 'requested_role', 'status', 'decided_at', 'created_at'])->toArray()],
            ],
            'intelligence-communication' => [
                ['title' => 'Messages', 'items' => AgentMessage::query()->latest()->limit(20)->get(['message_type', 'status', 'subject', 'created_at'])->toArray()],
            ],
            'intelligence-shared-memory' => [
                ['title' => 'Shared Memory', 'items' => EnterpriseAgentMemory::query()->latest()->limit(20)->get(['memory_key', 'memory_type', 'scope', 'title', 'created_at'])->toArray()],
            ],
            'intelligence-performance' => [
                ['title' => 'Execution Metrics', 'items' => AgentExecutionMetric::query()->latest()->limit(20)->get(['metric_key', 'metric_value', 'unit', 'created_at'])->toArray()],
            ],
            'intelligence-quality' => [
                ['title' => 'Quality Scores', 'items' => AgentQualityScore::query()->latest()->limit(20)->get(['overall_score', 'completeness_score', 'evidence_score', 'verification_score', 'created_at'])->toArray()],
            ],
            'intelligence-decision-records' => [
                ['title' => 'Decision Records', 'items' => AgentDecision::query()->latest()->limit(20)->get(['decision_key', 'chosen_path', 'confidence', 'created_at'])->toArray()],
                ['title' => 'Summaries', 'items' => AgentSummary::query()->latest()->limit(20)->get(['summary_type', 'summary', 'created_at'])->toArray()],
            ],
            'intelligence-monitoring' => [
                ['title' => 'Monitoring Summary', 'items' => [$this->agentMonitoring->summary()]],
            ],
            'intelligence-executive-dashboard' => [
                ['title' => 'Mission Portfolio', 'items' => EnterpriseMission::query()->latest()->limit(10)->get(['mission_key', 'title', 'status', 'priority', 'completion_percentage', 'health_score'])->toArray()],
                ['title' => 'Operations KPIs', 'items' => [$this->latestKpiPayload()]],
                ['title' => 'Operations Monitoring', 'items' => [$this->operationsMonitor->summary()]],
            ],
            'intelligence-enterprise-missions' => [
                ['title' => 'Enterprise Missions', 'items' => EnterpriseMission::query()->latest()->limit(20)->get(['mission_key', 'title', 'status', 'priority', 'budget_amount', 'deadline_at', 'completion_percentage'])->toArray()],
                ['title' => 'Mission Reports', 'items' => EnterpriseMission::query()->latest()->limit(5)->get()->map(fn (EnterpriseMission $mission): array => $this->missionReporting->report($mission))->toArray()],
            ],
            'intelligence-mission-timeline' => [
                ['title' => 'Mission Checkpoints', 'items' => MissionCheckpoint::query()->latest('recorded_at')->limit(20)->get(['checkpoint_type', 'status', 'recorded_at'])->toArray()],
                ['title' => 'Mission Milestones', 'items' => MissionMilestone::query()->latest()->limit(20)->get(['title', 'status', 'target_at', 'completed_at'])->toArray()],
                ['title' => 'Enterprise Events', 'items' => EnterpriseEvent::query()->latest()->limit(20)->get(['event_type', 'subject_type', 'created_at'])->toArray()],
            ],
            'intelligence-operations-centre' => [
                ['title' => 'Mission Executions', 'items' => MissionExecution::query()->latest()->limit(20)->get(['execution_key', 'status', 'execution_mode', 'assigned_team', 'last_checkpoint_at'])->toArray()],
                ['title' => 'Mission Dependencies', 'items' => MissionDependency::query()->latest()->limit(20)->get(['dependency_type', 'source_reference', 'target_reference', 'status'])->toArray()],
                ['title' => 'Mission Risks', 'items' => MissionRisk::query()->latest()->limit(20)->get(['title', 'severity', 'probability', 'impact_score', 'status'])->toArray()],
            ],
            'intelligence-execution-centre' => [
                ['title' => 'Execution Centre', 'items' => MissionExecution::query()->latest()->limit(20)->get(['execution_key', 'status', 'execution_mode', 'retry_count', 'started_at', 'completed_at'])->toArray()],
                ['title' => 'Mission Outcomes', 'items' => MissionOutcome::query()->latest()->limit(20)->get(['outcome_type', 'status', 'verification_score', 'confidence_score', 'created_at'])->toArray()],
            ],
            'intelligence-mission-planner' => [
                ['title' => 'Mission Plan Versions', 'items' => MissionPlanVersion::query()->latest('version_number')->limit(20)->get(['version_number', 'status', 'complexity', 'estimated_duration_hours', 'estimated_financial_cost', 'estimated_token_cost'])->toArray()],
                ['title' => 'Mission Objectives', 'items' => OperationsMissionObjective::query()->latest()->limit(20)->get(['title', 'objective_type', 'priority', 'status', 'completion_percentage'])->toArray()],
                ['title' => 'Mission Phases', 'items' => MissionPhase::query()->latest()->limit(20)->get(['name', 'phase_type', 'status', 'sequence', 'owner_team'])->toArray()],
            ],
            'intelligence-agent-marketplace' => [
                ['title' => 'Agent Catalog', 'items' => AgentCatalog::query()->latest()->limit(20)->get(['name', 'slug', 'provider', 'preferred_model', 'historical_success_rate', 'trust_score'])->toArray()],
                ['title' => 'Capabilities', 'items' => OperationsAgentCapability::query()->latest()->limit(20)->get(['capability_key', 'capability_type', 'confidence_score', 'created_at'])->toArray()],
                ['title' => 'Availability', 'items' => OperationsAgentAvailability::query()->latest()->limit(20)->get(['availability_status', 'capacity_percentage', 'available_at'])->toArray()],
                ['title' => 'Skills', 'items' => OperationsAgentSkill::query()->latest()->limit(20)->get(['skill_key', 'skill_level', 'language', 'created_at'])->toArray()],
            ],
            'intelligence-simulation-studio' => [
                ['title' => 'Scenario Simulations', 'items' => ScenarioSimulation::query()->latest()->limit(20)->get(['scenario_type', 'scenario_label', 'status', 'created_at'])->toArray()],
            ],
            'intelligence-predictions' => [
                ['title' => 'Predictions', 'items' => MissionPrediction::query()->latest()->limit(20)->get(['prediction_type', 'prediction_window', 'confidence_score', 'predicted_value', 'created_at'])->toArray()],
            ],
            'intelligence-operations-compliance' => [
                ['title' => 'Compliance Reviews', 'items' => MissionComplianceReview::query()->latest()->limit(20)->get(['framework', 'status', 'compliance_score', 'created_at'])->toArray()],
            ],
            'intelligence-operations-policies' => [
                ['title' => 'Policy Evaluations', 'items' => MissionPolicy::query()->latest()->limit(20)->get(['policy_type', 'status', 'evaluation_score', 'created_at'])->toArray()],
            ],
            'intelligence-operations-approvals' => [
                ['title' => 'Mission Approvals', 'items' => OperationsMissionApproval::query()->latest()->limit(20)->get(['approval_type', 'requested_role', 'status', 'due_at', 'decided_at'])->toArray()],
            ],
            'intelligence-enterprise-monitoring' => [
                ['title' => 'Enterprise Monitoring', 'items' => [$this->operationsMonitor->summary()]],
                ['title' => 'Agent Providers', 'items' => OperationsAgentProvider::query()->latest()->limit(20)->get(['provider_key', 'status', 'latency_ms', 'health_score'])->toArray()],
            ],
            'intelligence-operations-decision-records' => [
                ['title' => 'Decision Records', 'items' => MissionDecisionRecord::query()->latest()->limit(20)->get(['decision_key', 'chosen_option', 'confidence_score', 'review_at', 'created_at'])->toArray()],
                ['title' => 'Learning Cycles', 'items' => MissionLearningCycle::query()->latest()->limit(20)->get(['learning_type', 'status', 'improvement_summary', 'created_at'])->toArray()],
            ],
            'intelligence-enterprise-kpis' => [
                ['title' => 'KPI Snapshots', 'items' => EnterpriseKpiSnapshot::query()->latest('recorded_at')->limit(20)->get(['snapshot_key', 'recorded_at', 'created_at'])->toArray()],
                ['title' => 'Mission Metrics', 'items' => MissionMetric::query()->latest('recorded_at')->limit(20)->get(['metric_key', 'metric_value', 'unit', 'recorded_at'])->toArray()],
            ],
            'intelligence-autonomy-analytics' => [
                ['title' => 'Autonomy Metrics', 'items' => MissionMetric::query()->where('metric_key', 'autonomy_percentage')->latest('recorded_at')->limit(20)->get(['metric_key', 'metric_value', 'unit', 'recorded_at'])->toArray()],
                ['title' => 'KPI Rollup', 'items' => [$this->latestKpiPayload()]],
            ],
            'intelligence-commercial-packages' => [
                ['title' => 'Package Catalog', 'items' => IntelligencePackage::query()->latest()->limit(20)->get(['name', 'tier', 'support_level', 'sla_level', 'monthly_execution_limit', 'token_usage_limit'])->toArray()],
            ],
            'intelligence-commercial-tenants' => [
                ['title' => 'Tenant Portfolio', 'items' => IntelligenceTenant::query()->latest()->limit(20)->get(['name', 'industry', 'status', 'deployment_mode', 'primary_contact_email'])->toArray()],
            ],
            'intelligence-commercial-provisioning' => [
                ['title' => 'Provisioning Queue', 'items' => TenantProvisioningRequest::query()->latest()->limit(20)->get(['status', 'deployment_mode', 'go_live_target_at', 'created_at'])->toArray()],
            ],
            'intelligence-commercial-subscriptions' => [
                ['title' => 'Active Subscriptions', 'items' => IntelligenceSubscription::query()->latest()->limit(20)->get(['status', 'monthly_price', 'currency', 'starts_at', 'renews_at'])->toArray()],
            ],
            'intelligence-commercial-usage' => [
                ['title' => 'Usage Meters', 'items' => UsageMeter::query()->latest()->limit(20)->get(['meter_key', 'usage_total', 'usage_limit', 'warning_threshold', 'period_ends_at'])->toArray()],
                ['title' => 'Overages', 'items' => UsageOverage::query()->latest()->limit(20)->get(['overage_key', 'quantity', 'status', 'created_at'])->toArray()],
            ],
            'intelligence-commercial-billing' => [
                ['title' => 'Billing Accounts', 'items' => BillingAccount::query()->latest()->limit(20)->get(['account_name', 'billing_email', 'currency', 'status', 'created_at'])->toArray()],
            ],
            'intelligence-commercial-proposals' => [
                ['title' => 'Proposal Pipeline', 'items' => IntelligenceProposal::query()->latest()->limit(20)->get(['title', 'stage', 'currency', 'estimated_monthly_value', 'sent_at'])->toArray()],
            ],
            'intelligence-commercial-deployments' => [
                ['title' => 'Deployment Runbooks', 'items' => DeploymentRunbook::query()->latest()->limit(20)->get(['name', 'status', 'deployment_mode', 'created_at'])->toArray()],
            ],
            'intelligence-commercial-support' => [
                ['title' => 'Support Tickets', 'items' => SupportTicket::query()->latest()->limit(20)->get(['title', 'status', 'severity', 'opened_at', 'resolved_at'])->toArray()],
            ],
            'intelligence-commercial-readiness' => [
                ['title' => 'Release Readiness', 'items' => ReleaseReadinessCheck::query()->latest()->limit(20)->get(['status', 'score', 'checked_at', 'created_at'])->toArray()],
            ],
            'intelligence-commercial-settings' => [
                ['title' => 'Commercial Signals', 'items' => [[
                    'packages' => class_exists(IntelligencePackage::class) ? IntelligencePackage::query()->count() : 0,
                    'tenants' => class_exists(IntelligenceTenant::class) ? IntelligenceTenant::query()->count() : 0,
                    'subscriptions' => class_exists(IntelligenceSubscription::class) ? IntelligenceSubscription::query()->count() : 0,
                    'proposals' => class_exists(IntelligenceProposal::class) ? IntelligenceProposal::query()->count() : 0,
                ]]],
            ],
            'intelligence-agent-settings' => [
                ['title' => 'Multi-Agent Settings', 'items' => [[
                    'enabled' => (string) (config('intelligence.agent_runtime.multi_agent.enabled') ? 'true' : 'false'),
                    'default_execution_mode' => (string) config('intelligence.agent_runtime.multi_agent.default_execution_mode'),
                    'default_approval_role' => (string) config('intelligence.agent_runtime.multi_agent.default_approval_role'),
                    'monitoring_window' => (string) config('intelligence.agent_runtime.multi_agent.monitoring_window'),
                ]]],
            ],
            'intelligence-diagnostics' => [
                ['title' => 'Diagnostics', 'items' => [
                    [
                        'default_provider' => (string) config('intelligence.default_provider'),
                        'default_model' => (string) config('intelligence.default_model'),
                        'streaming_enabled' => (string) (config('intelligence.streaming.enabled') ? 'true' : 'false'),
                    ],
                ]],
            ],
            default => [],
        };
    }

    private function latestKpiPayload(): array
    {
        $snapshot = EnterpriseKpiSnapshot::query()->latest('recorded_at')->first();

        return $snapshot?->kpi_payload ?? $this->kpis->snapshot()->kpi_payload;
    }
}
