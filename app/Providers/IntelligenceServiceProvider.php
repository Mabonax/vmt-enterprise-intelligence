<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domains\Intelligence\Agents\Monitoring\AgentMonitoringService;
use App\Domains\Intelligence\Agents\Services\AgentRoleCatalog;
use App\Domains\Intelligence\Agents\Services\MultiAgentCoordinator;
use App\Domains\Intelligence\Contracts\Agent;
use App\Domains\Intelligence\Contracts\AiProvider;
use App\Domains\Intelligence\Contracts\ConversationRepository as ConversationRepositoryContract;
use App\Domains\Intelligence\Contracts\EmbeddingProvider;
use App\Domains\Intelligence\Contracts\MemoryStore;
use App\Domains\Intelligence\Contracts\StreamingProvider;
use App\Domains\Intelligence\Contracts\TokenCounter;
use App\Domains\Intelligence\Contracts\Tool;
use App\Domains\Intelligence\Repositories\ConversationRepository;
use App\Domains\Intelligence\Services\AgentResolver;
use App\Domains\Intelligence\Services\ArrayMemoryStore;
use App\Domains\Intelligence\Services\ConnectorManager;
use App\Domains\Intelligence\Services\ModelRouter;
use App\Domains\Intelligence\Services\OpenApiToolImporter;
use App\Domains\Intelligence\Services\PlanningEngine;
use App\Domains\Intelligence\Services\ProviderManager;
use App\Domains\Intelligence\Services\ReplayEngine;
use App\Domains\Intelligence\Services\ToolApprovalService;
use App\Domains\Intelligence\Services\ToolAnalyticsService;
use App\Domains\Intelligence\Services\ToolAuthorizationService;
use App\Domains\Intelligence\Services\ToolCompatibilityService;
use App\Domains\Intelligence\Services\ToolDiscovery;
use App\Domains\Intelligence\Services\ToolDispatcher;
use App\Domains\Intelligence\Services\ToolExecutor;
use App\Domains\Intelligence\Services\ToolHealthMonitor;
use App\Domains\Intelligence\Services\ToolInstaller;
use App\Domains\Intelligence\Services\ToolMarketplaceService;
use App\Domains\Intelligence\Services\ToolPublisher;
use App\Domains\Intelligence\Services\ToolRegistry;
use App\Domains\Intelligence\Services\ToolResolver;
use App\Domains\Intelligence\Services\ToolUsageTracker;
use App\Domains\Intelligence\Services\ToolValidator;
use App\Domains\Intelligence\Services\ToolVersionManager;
use App\Domains\Intelligence\Services\VerificationEngine;
use App\Domains\Intelligence\Services\WorkflowRuntime;
use App\Domains\Intelligence\Knowledge\Repositories\KnowledgeDocumentRepository;
use App\Domains\Intelligence\Knowledge\Repositories\KnowledgeMemoryRepository;
use App\Domains\Intelligence\Knowledge\Services\CitationService as KnowledgeCitationService;
use App\Domains\Intelligence\Knowledge\Services\DocumentChunker;
use App\Domains\Intelligence\Knowledge\Services\EmbeddingService;
use App\Domains\Intelligence\Knowledge\Services\KnowledgeAnalyticsService;
use App\Domains\Intelligence\Knowledge\Services\KnowledgeExtractor;
use App\Domains\Intelligence\Knowledge\Services\KnowledgeGraphService;
use App\Domains\Intelligence\Knowledge\Services\KnowledgeHealthService;
use App\Domains\Intelligence\Knowledge\Services\KnowledgeLifecycleService;
use App\Domains\Intelligence\Knowledge\Services\KnowledgeRetrievalService;
use App\Domains\Intelligence\Knowledge\Services\KnowledgeScoringService;
use App\Domains\Intelligence\Knowledge\Services\LearningService;
use App\Domains\Intelligence\Knowledge\Services\MemoryConsolidationService;
use App\Domains\Intelligence\Knowledge\Services\MemoryService as KnowledgeMemoryService;
use App\Domains\Intelligence\Knowledge\Services\RelationshipEngine;
use App\Domains\Intelligence\Knowledge\Services\SemanticSearchService;
use App\Domains\Intelligence\Operations\Services\AgentTeamBuilder;
use App\Domains\Intelligence\Operations\Services\ApprovalWorkflowService;
use App\Domains\Intelligence\Operations\Services\ComplianceVerifier;
use App\Domains\Intelligence\Operations\Services\DecisionAnalysisService;
use App\Domains\Intelligence\Operations\Services\EnterpriseEventBus;
use App\Domains\Intelligence\Operations\Services\ExecutiveOrchestrator;
use App\Domains\Intelligence\Operations\Services\ExecutionSupervisor;
use App\Domains\Intelligence\Operations\Services\KpiCalculationService;
use App\Domains\Intelligence\Operations\Services\LearningOptimizationService;
use App\Domains\Intelligence\Operations\Services\MissionExecutionService;
use App\Domains\Intelligence\Operations\Services\MissionHealthService;
use App\Domains\Intelligence\Operations\Services\MissionPlanner;
use App\Domains\Intelligence\Operations\Services\MissionReportingService;
use App\Domains\Intelligence\Operations\Services\OperationsMonitor;
use App\Domains\Intelligence\Operations\Services\PolicyEngine;
use App\Domains\Intelligence\Operations\Services\PredictionEngine;
use App\Domains\Intelligence\Operations\Services\ScenarioSimulator;
use App\Domains\Intelligence\Tools\Sandbox\ToolExecutionSandbox;
use App\Domains\Intelligence\Tools\Security\SecretVaultService;
use App\Domains\Intelligence\Tools\Support\ToolManifestParser;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use ReflectionClass;
use Throwable;

class IntelligenceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $providerClasses = $this->discoverContractsIn(app_path('Domains/Intelligence/Providers'), AiProvider::class);
        $toolClasses = $this->discoverContractsIn(app_path('Domains/Intelligence/Tools'), Tool::class);
        $agentClasses = $this->discoverContractsIn(app_path('Domains/Intelligence/Agents'), Agent::class);

        $this->app->singleton('intelligence.providers', static fn (): array => $providerClasses);
        $this->app->singleton('intelligence.tools', static fn (): array => $toolClasses);
        $this->app->singleton('intelligence.agents', static fn (): array => $agentClasses);

        $this->app->bind(ConversationRepositoryContract::class, ConversationRepository::class);
        $this->app->singleton(MemoryStore::class, ArrayMemoryStore::class);

        $this->app->singleton(ToolDispatcher::class, fn ($app): ToolDispatcher => new ToolDispatcher(
            container: $app,
            toolClasses: $app->make('intelligence.tools'),
        ));
        $this->app->singleton(ToolManifestParser::class);
        $this->app->singleton(KnowledgeDocumentRepository::class);
        $this->app->singleton(KnowledgeMemoryRepository::class);
        $this->app->singleton(DocumentChunker::class);
        $this->app->singleton(EmbeddingService::class);
        $this->app->singleton(KnowledgeExtractor::class);
        $this->app->singleton(RelationshipEngine::class);
        $this->app->singleton(KnowledgeScoringService::class);
        $this->app->singleton(KnowledgeGraphService::class);
        $this->app->singleton(KnowledgeCitationService::class);
        $this->app->singleton(KnowledgeMemoryService::class);
        $this->app->singleton(SemanticSearchService::class);
        $this->app->singleton(KnowledgeRetrievalService::class);
        $this->app->singleton(KnowledgeLifecycleService::class);
        $this->app->singleton(KnowledgeHealthService::class);
        $this->app->singleton(LearningService::class);
        $this->app->singleton(MemoryConsolidationService::class);
        $this->app->singleton(KnowledgeAnalyticsService::class);
        $this->app->singleton(ToolDiscovery::class);
        $this->app->singleton(ToolValidator::class);
        $this->app->singleton(ToolVersionManager::class);
        $this->app->singleton(ToolPublisher::class);
        $this->app->singleton(ToolInstaller::class);
        $this->app->singleton(ToolHealthMonitor::class);
        $this->app->singleton(ToolUsageTracker::class);
        $this->app->singleton(ToolAnalyticsService::class);
        $this->app->singleton(ToolMarketplaceService::class);
        $this->app->singleton(ToolCompatibilityService::class);
        $this->app->singleton(ToolAuthorizationService::class);
        $this->app->singleton(ToolExecutionSandbox::class);
        $this->app->singleton(SecretVaultService::class);
        $this->app->singleton(ConnectorManager::class);
        $this->app->singleton(OpenApiToolImporter::class);
        $this->app->singleton(ToolRegistry::class, fn ($app): ToolRegistry => new ToolRegistry(
            $app,
            $app->make('intelligence.tools'),
            $app->make(ToolDiscovery::class),
            $app->make(ToolValidator::class),
            $app->make(ToolVersionManager::class),
            $app->make(ToolPublisher::class),
        ));
        $this->app->singleton(ToolResolver::class, fn ($app): ToolResolver => new ToolResolver($app, $app->make('intelligence.tools')));
        $this->app->singleton(ToolApprovalService::class);
        $this->app->singleton(ToolExecutor::class);
        $this->app->singleton(ReplayEngine::class);
        $this->app->singleton(AgentResolver::class);
        $this->app->singleton(AgentRoleCatalog::class);
        $this->app->singleton(MultiAgentCoordinator::class);
        $this->app->singleton(AgentMonitoringService::class);
        $this->app->singleton(ProviderManager::class, fn ($app): ProviderManager => new ProviderManager(
            container: $app,
            providerClasses: $app->make('intelligence.providers'),
        ));
        $this->app->singleton(ModelRouter::class);
        $this->app->singleton(PlanningEngine::class);
        $this->app->singleton(VerificationEngine::class);
        $this->app->singleton(WorkflowRuntime::class);
        $this->app->singleton(EnterpriseEventBus::class);
        $this->app->singleton(MissionHealthService::class);
        $this->app->singleton(PolicyEngine::class);
        $this->app->singleton(ComplianceVerifier::class);
        $this->app->singleton(AgentTeamBuilder::class);
        $this->app->singleton(MissionPlanner::class);
        $this->app->singleton(ApprovalWorkflowService::class);
        $this->app->singleton(MissionExecutionService::class);
        $this->app->singleton(ExecutionSupervisor::class);
        $this->app->singleton(ScenarioSimulator::class);
        $this->app->singleton(PredictionEngine::class);
        $this->app->singleton(KpiCalculationService::class);
        $this->app->singleton(DecisionAnalysisService::class);
        $this->app->singleton(LearningOptimizationService::class);
        $this->app->singleton(MissionReportingService::class);
        $this->app->singleton(OperationsMonitor::class);
        $this->app->singleton(ExecutiveOrchestrator::class);

        $this->bindDefaultProvider($providerClasses);
    }

    public function boot(): void
    {
        try {
            if (Schema::hasTable('ai_tools')) {
                $this->app->make(ToolRegistry::class)->syncDiscoveredTools();
            }

            if (Schema::hasTable('connector_registrations')) {
                $this->app->make(ConnectorManager::class)->syncRegistrations();
            }

            if (Schema::hasTable('agent_roles')) {
                $this->app->make(AgentRoleCatalog::class)->sync();
            }
        } catch (Throwable) {
            // Allow console bootstrap and test discovery to continue even if the
            // current database is unavailable or not yet migrated.
        }

        config([
            'intelligence.discovered.providers' => $this->app->make('intelligence.providers'),
            'intelligence.discovered.tools' => $this->app->make('intelligence.tools'),
            'intelligence.discovered.agents' => $this->app->make('intelligence.agents'),
        ]);
    }

    /**
     * @param list<class-string<AiProvider>> $providerClasses
     */
    private function bindDefaultProvider(array $providerClasses): void
    {
        $this->app->singleton(AiProvider::class, fn ($app): AiProvider => $app->make(ProviderManager::class)->resolve());

        $this->app->bind(EmbeddingProvider::class, AiProvider::class);
        $this->app->bind(StreamingProvider::class, AiProvider::class);
        $this->app->bind(TokenCounter::class, AiProvider::class);
    }

    /**
     * @template T of object
     * @param class-string<T> $contract
     * @return list<class-string<T>>
     */
    private function discoverContractsIn(string $directory, string $contract): array
    {
        if (! File::exists($directory)) {
            return [];
        }

        $classes = [];
        $basePath = app_path('Domains/Intelligence');
        $baseNamespace = 'App\\Domains\\Intelligence\\';

        foreach (File::allFiles($directory) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $relativePath = str_replace(
                ['/', '.php'],
                ['\\', ''],
                ltrim(str_replace($basePath, '', $file->getPathname()), '\\/'),
            );

            $class = $baseNamespace.str_replace('\\\\', '\\', $relativePath);

            if (! class_exists($class)) {
                continue;
            }

            $reflection = new ReflectionClass($class);

            if ($reflection->isAbstract() || $reflection->isInterface()) {
                continue;
            }

            if (is_subclass_of($class, $contract)) {
                $classes[] = $class;
            }
        }

        sort($classes);

        return $classes;
    }
}
