# Domain Overview

The platform already contains the bounded contexts required for the Enterprise AI Gateway mission. Phase 11 keeps them in place and clarifies their gateway-specific responsibilities.

## Core gateway contexts

- `Runtime`: execution plans, execution traces, prompt assembly, verification, replay, streaming, and background execution
- `Providers`: provider contracts and adapters used by runtime routing
- `Tools`: callable capabilities, approvals, sandboxing, versioning, health, and execution audit
- `Connectors`: enterprise integration adapters used by tools and future ERP action surfaces
- `Knowledge`: ingestion, chunking, embeddings, semantic search, citations, graph, learning, and retrieval
- `Agents`: optional multi-agent planning, delegation, reasoning, and approval flows
- `Operations`: internal orchestration, missions, predictions, policies, approvals, KPI reporting, and supervision
- `Commercial`: deployment planning, provisioning, packaging, subscriptions, licensing, support, readiness, and handover
- `Admin Console`: command-centre dashboards, alerts, actions, health, readiness, and audit

## Supporting contexts

- `Administration` and `Identity`: platform roles, policies, and authenticated operator access
- `Organizations` and `Connections`: tenant and ERP boundary modelling
- `Conversations`, `Memory`, and `PromptLibrary`: runtime support services used to stabilize AI execution
- `Monitoring`, `Audit`, `Notifications`, `Settings`, `Health`, `Search`, `Storage`, and `Analytics`: operational support contexts that strengthen observability and control

## Repositioning guidance

- Do not delete or duplicate these contexts
- Do not turn the platform into a replacement ERP
- Do not position knowledge, agents, or operations as standalone products
- Reuse the existing domain seams to expose ERP-safe gateway capabilities such as summarisation, explanation, reporting, retrieval, classification, drafting, and approved action execution
