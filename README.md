# VMT Enterprise AI Gateway

VMT Enterprise AI Gateway is the centralized AI integration layer for VMT ERP systems. This repository is an existing Laravel 12 + Inertia React + TypeScript platform that already contains the bounded contexts needed for runtime execution, providers, tools, connectors, knowledge retrieval, agents, operations, commercial deployment management, and the admin console.

The current Phase 11 objective is a repositioning, not a rewrite:

- Every ERP should call this gateway for AI work
- Local AI is the default deployment model
- Cloud providers are optional and disabled by default
- Runtime remains the execution engine
- Existing bounded contexts are retained and refocused around ERP-safe AI delivery

## What already exists

- Runtime execution, planning, verification, replay, and trace persistence
- Provider discovery and container bindings behind `AiProvider`
- Tool registry, execution, approval, sandbox, and usage tracking
- Connector registry for REST, database, queue, webhook, storage, email, and related drivers
- Knowledge ingestion, chunking, embeddings, retrieval, graph, citations, and health services
- Optional multi-agent and operations orchestration flows
- Commercial deployment, provisioning, subscription, usage, support, and readiness services
- Admin Console monitoring, alerts, actions, audit, health, and readiness views
- Docker, queue, scheduler, Redis, and seeded platform scaffolding

## Current repositioning status

- The runtime and bounded contexts are real and reusable
- The provider layer is still stub-backed and needs production adapters
- The API surface is still biased toward an internal intelligence workspace and must gain an ERP-specific gateway contract
- Commercial and admin surfaces need wording and metrics aligned to deployment operations rather than a generic AI platform narrative

See [Enterprise AI Gateway Specification](docs/enterprise-ai-gateway-specification.md), [Phase 11 Repositioning Plan](docs/phase-11-enterprise-ai-gateway-repositioning.md), [Architecture](docs/Architecture.md), [Domain Overview](docs/Domain%20Overview.md), and [Roadmap](docs/Roadmap.md).

## Quick start

```bash
composer install
npm install
php artisan migrate --seed
npm run build
php artisan serve
```

## Default administrator

- Email: `admin@vip.local`
- Password: `password`

Override these with `VIP_ADMIN_NAME`, `VIP_ADMIN_EMAIL`, and `VIP_ADMIN_PASSWORD`.

## Docker

```bash
docker compose up --build
```

The app is exposed at `http://localhost:8080`.
