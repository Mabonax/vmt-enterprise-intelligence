# Roadmap

## Completed groundwork

### Phases 1-10

- Foundation, auth, navigation, migrations, Docker, and domain-first scaffolding
- Runtime, provider contracts, tools, connectors, knowledge, agents, operations, commercial, and admin console bounded contexts
- Additive UI and API surfaces for internal platform management

## Current phase

### Phase 11: Enterprise AI Gateway repositioning

- Audit the current implementation against the ERP gateway mission
- Preserve existing bounded contexts and refocus their responsibilities
- Introduce an ERP-facing gateway API contract
- Replace stub providers with production local-first adapters
- Normalize local-first configuration, deployment defaults, and security controls
- Rewrite core documentation around the Enterprise AI Gateway narrative

See [Phase 11 Repositioning Plan](phase-11-enterprise-ai-gateway-repositioning.md).

## Next implementation sequence

### Phase 11A: Gateway contract

- Add ERP authentication, ERP client registration, and gateway capability endpoints
- Define stable request and response DTOs for ERP-safe AI capabilities
- Introduce gateway audit logging and ERP request correlation

### Phase 11B: Local provider rollout

- Deliver production Ollama support first
- Add local OpenAI-compatible server support next
- Add `llama.cpp` server and `vLLM` adapters afterwards
- Keep cloud providers behind explicit enablement flags

### Phase 11C: Operational hardening

- Expand admin console provider, connector, ERP, and queue telemetry
- Add egress policy enforcement and provider allowlists
- Add deployment runbooks and local AI readiness checks

### Phase 11D: ERP adoption

- Integrate the first VMT ERP through the new gateway API
- Validate end-to-end request tracing, knowledge retrieval, approvals, and action execution
- Freeze and document the ERP contract for reuse across future VMT ERP deployments
