# Phase 5 Enterprise Connector Platform

Phase 5 extends the existing Intelligence bounded context into a governed enterprise tool execution platform without replacing the Phase 1-4 runtime.

## What Phase 5 Adds

- An additive enterprise tool catalog in `enterprise_tools` with categories, packages, versions, dependencies, health, usage, costs, executions, graph nodes, test runs, marketplace records, SDK exports, and execution streams.
- A connector framework under `app/Domains/Intelligence/Connectors` for Laravel, REST, GraphQL, database, filesystem, Redis, cache, queue, webhook, OpenAPI, MCP, email, calendar, and storage execution paths.
- A bridge inside `App\Domains\Intelligence\Services\ToolRegistry` so the existing `ai_tools` runtime remains intact while the richer enterprise registry is synchronized in parallel.
- Connector-backed execution inside `App\Domains\Intelligence\Services\ToolExecutor` with sandbox limits, authorization metadata, usage tracking, health updates, replay payload capture, and execution streams.
- Encrypted secret storage through `App\Domains\Intelligence\Tools\Security\SecretVaultService`.
- OpenAPI-to-tool manifest generation through `App\Domains\Intelligence\Services\OpenApiToolImporter`.
- Expanded Intelligence workspace pages for marketplace, connectors, credentials, analytics, health, execution graph, execution history, testing, SDK, and costs.

## Runtime Flow

1. `IntelligenceServiceProvider` discovers classic `IntelligenceTool` classes.
2. `ToolRegistry` keeps the legacy `ai_tools` sync behavior and also projects those tools into `enterprise_tools`.
3. JSON manifests under `app/Domains/Intelligence/Tools/Manifests` are loaded into the same enterprise registry.
4. `ConnectorManager` registers available connector drivers and records connector health.
5. `ToolExecutor` resolves enterprise tools first. If present, execution is sandboxed and routed through the configured connector.
6. Usage, cost, health, stream, and replay records are written after each execution.
7. The existing phase-4 runtime path remains available for legacy tool execution and current tests.

## Key Extension Points

- Add new enterprise tools with PHP attributes or `tool.json`-style manifests.
- Point Laravel connector tools at service classes and methods through tool metadata.
- Import external OpenAPI documents and translate operations into executable tool manifests.
- Store connector credentials in the vault and reference them from tool metadata rather than prompt payloads.
- Use the execution graph and replay payloads as the debugging spine for future clustered orchestration.
