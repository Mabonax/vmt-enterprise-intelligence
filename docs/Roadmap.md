# Roadmap

The project is now managed by product readiness rather than additive architecture phases.

## Product identity

VMT Enterprise AI Gateway is the reusable intelligence middleware between VMT ERP products and AI runtimes.

The ERP owns business workflows. The gateway owns governed AI access. The runtime owns inference.

## Readiness level 1: Foundation — complete

Delivered:

- Laravel 12 + Inertia platform foundation
- authentication and administration
- runtime execution, planning, verification, replay, and tracing
- provider abstraction
- tools and connector framework
- knowledge ingestion and retrieval
- multi-agent and operations bounded contexts
- deployment/commercial governance
- admin console
- Docker, queues, scheduler, PostgreSQL, and Redis

## Readiness level 2: Gateway integration — substantially complete

Delivered:

- ERP-facing capability contract
- chat, summarise, report, translate, classify, search, and action endpoints
- gateway client and tenant models
- API key, HMAC, and JWT authentication
- replay protection
- capability, model, and provider authorization
- tenant isolation controls
- audit logging and request correlation
- provider allowlists and local-first egress controls
- usage enforcement and security event logging
- production Ollama adapter
- real local model defaults
- production blocking of scaffold providers

Remaining:

- first real VMT ERP integration
- freeze an integration SDK/contract from that implementation

## Readiness level 3: Deployment readiness — active

Current materialization work:

- Docker-managed Ollama runtime
- automatic bootstrap of the default chat and embedding models
- gateway runtime readiness checks
- deployment acceptance criteria
- operator-facing visibility for connected applications, provider/model state, credentials, traces, and security events
- non-mocked acceptance tests against an actual Ollama runtime

A deployment reaches this level only when `php artisan gateway:readiness` succeeds and a real gateway request completes against the configured runtime.

## Readiness level 4: Production readiness — pending

Required:

- hardened production environment profile
- TLS and secret-management runbook
- backup/restore validation
- queue and scheduler health monitoring
- model storage and disk-capacity monitoring
- rate-limit and load testing
- failure/retry/fallback acceptance tests
- tenant isolation verification against realistic datasets
- approved operational support runbook
- end-to-end observability from ERP request through inference and response

## Readiness level 5: Multi-ERP adoption — pending

Required:

- onboard the second and third VMT ERP without changing provider-facing code
- demonstrate shared capability contracts across products
- demonstrate provider/model replacement without ERP rewrites
- establish reusable ERP client SDKs
- establish repeatable customer deployment and upgrade procedures

## Immediate implementation order

1. Complete Materialization Phase 1 local runtime readiness.
2. Build the Connected Applications operator workflow.
3. Integrate the first real VMT ERP.
4. Run a non-mocked end-to-end acceptance suite.
5. Freeze Gateway Contract v1.
6. Harden production deployment.
7. Add additional provider adapters only after the Ollama path is proven operational.

Agents, commercial features, and advanced orchestration should not expand further until the first production-style ERP integration is proven.
