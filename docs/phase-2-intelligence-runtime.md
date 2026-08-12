# Phase 2 Intelligence Runtime

Phase 2 introduces the `App\Domains\Intelligence` bounded context as the provider-neutral runtime that sits between enterprise applications and future AI providers.

## Scope

- Conversation persistence and lifecycle services
- Prompt and context assembly
- Token accounting and overflow protection
- Usage tracking and export scaffolding
- Tool and agent contracts
- Provider discovery and stub bindings
- Admin workspace routing and placeholder dashboards

## Explicit Non-Scope

- No provider SDK integrations
- No retrieval-augmented generation
- No document ingestion
- No ERP connector workflows
- No business-specific agent or tool logic

## Runtime Layers

- `Contracts`: shared provider, conversation, tool, agent, token, and memory abstractions
- `DTOs`: immutable request, response, usage, and prompt pipeline data structures
- `Models`: UUID conversation, message, and attachment persistence
- `Repositories`: Eloquent-backed conversation persistence
- `Services`: orchestration, prompt assembly, context assembly, token management, citations, streaming, exports, and usage tracking
- `Providers`: stub provider implementations that satisfy the unified runtime contract

## Operational Notes

- `config/intelligence.php` contains deploy-safe defaults
- `App\Providers\IntelligenceServiceProvider` discovers providers, agents, and tools at boot
- Intelligence admin routes live under `/intelligence/*`
