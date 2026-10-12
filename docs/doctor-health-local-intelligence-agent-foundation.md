# Doctor Health Local Intelligence and Agent Foundation

Status: implementation contract / pending engineering acceptance
Baseline: GPERP `d107a3988586b4935c5d0edd7931d864e208e1fb`; Gateway `0d7728cc93893a567ecfb98103ee0fb96d62b8f1`.

## Existing integration — do not replace

Doctor Health's `EnterpriseAiAssistantCapabilityService` authorizes an ERP user, resolves a specific operational capability, provides scoped facts, and generates proposals. `EnterpriseAiConversationalAssistantService` sends these facts to `EnterpriseAiService::operationalAssistant`. Doctor Health retains the business record, authorization decision and workflow action. In particular, the backup dialogue navigates to Backup Operations; it does **not** start a backup.

The Gateway provides authenticated capability endpoints, isolated client/tenant context, model routing, retrieval, verification, audit, and a real Ollama adapter. The dedicated deployment contract documents the local Windows acceptance profile and explicitly identifies a live end-to-end request as a separate acceptance gate. Do not describe a working local inference installation merely because these code paths exist.

## Security and privacy boundaries

1. Dedicated deployment per customer; no public SaaS registration; local inference by default; cloud provider disabled.
2. ERP owns actor authorization and business operations. Gateway verifies client, tenant, scopes, model/provider policy, and request correlation. No unrestricted SQL or shell tool for an LLM.
3. Never ingest patient data, consultations, identifiers, messages, or secrets into reusable model weights or shared knowledge collections. Use approved redacted/synthetic fixtures for evaluation. Any live record context is minimized and ephemeral according to an explicit retention policy.
4. Audit metadata and error messages must not leak prompt bodies, personal or clinical data. Review existing request/trace storage and define encrypted retention and purge before production patient use.
5. Retrieval indexing is local, versioned, tenant-isolated, source-attributed, and revocable. Treat retrieved content as untrusted; retrieved text cannot grant tool permissions.
6. New agent actions start read-only or proposal-only; backup, security containment, messaging and external exports require a deterministic ERP tool plus approval or independently predefined policy.

## Phase 1 — verifiable local model acceptance

- Add an operator-only acceptance command/test that calls authenticated GPERP -> Gateway -> local Ollama and verifies provider/model, response correlation, tenant identity and audited completion, using synthetic fixtures.
- Assert negative tests for wrong client/ERP identity, cross-tenant organization, disabled provider and unavailable model.
- Document local offline networking, model file installation, encrypted storage, database separation, operator secret rotation, queue/scheduler supervision and restore acceptance.
- Measure latency, CPU/GPU memory pressure, context window and fail-closed behavior. Make no performance claim until measured.

## Phase 2 — ERP knowledge registry

Define a versioned manifest of allowed source material (ERP manuals, permitted workflows, API contracts, capabilities, business rules, policies). Each item records source id, version/hash, owner, sensitivity classification, tenant, permitted roles, effective/revocation dates, and last indexed state.

- Support ingest -> parse -> chunk -> local embedding -> isolated retrieval -> cited answer -> reindex/revoke.
- Include explicit data classification and deny patient-level records by default.
- Ensure scope filters are enforced server-side, not inferred from prompt text.
- Evaluate retrieval correctness and refusals against a small synthetic QA set.
- Model upgrades re-use the registry; re-embed when the embedding model/dimensions change. Fine-tuning is not required for day-to-day ERP updates.

## Phase 3 — governed agent runtime

Agents run as scheduled or event-driven queued jobs with explicit service identities, leased execution, idempotency keys, retry ceilings, failure reporting, approval expiry, and immutable audit references. LLMs may interpret results but never invent new authority.

First agents:

| Agent | Initial abilities | Action boundary |
| --- | --- | --- |
| Executive reporting | Read approved aggregate appointment, utilisation, revenue and queue data; render cited management brief | Read only; reports saved through a reviewed deterministic service |
| Backup integrity | Read backup schedule/history, checksum and off-machine copy status; warn on missing or failed backups | Backup creation/restore delegated to existing backup service; restore requires separate approval and test environment |
| Security/Defender | Read sanitized security events, authentication anomalies, permission changes and bulk-access metrics | Alerts/proposals only; token revocation or account suspension requires explicit policy and approval |

Agent identity must map to **separate** allowlisted scopes, not inherit a CEO or administrative user's full privileges. Cross-agent delegation cannot expand scope.

## Phase 4 — human approval and evidence

- Explain each proposed action with source ids, evidence timestamp, target resource and expected effects.
- Require a fresh ERP authorization check at execution time (not merely when drafted).
- Deny stale or replayed approvals; record approver, action version, expiry, result and correlation id.
- Produce clear UI states: observed, proposed, awaiting approval, approved, executing, verified, failed.
- Report numerical facts from authoritative ERP tools, not ungrounded model output.

## Tests and release gates

- Unit: manifest validation, chunk/access filtering, approval policy, idempotency, expired approval, retry behavior.
- Feature: actor/client/tenant separation, denied clinical records, offline-only provider, revoked documentation, backup proposal without execution.
- Integration: GPERP synthetic acceptance against real local Ollama; unexpected provider failure generates safe 502 and an auditable failed trace.
- Operational: offline test with outbound Internet blocked, recovery drill, backup verification, no sensitive prompts in exported telemetry.
- Do not merge a capability as production-ready without tests, a demonstration path and release notes.

## Implementation order

1. Inspect existing Gateway knowledge, tooling, agent and approval models; reuse rather than create parallel engines.
2. Close prompt/trace storage leakage and actor/service identity gaps before enabling any live clinical context.
3. Implement Phase 1 acceptance; then knowledge manifest and retrieval authorization; then read-only executive agent; then backup and defender observers.
4. Make individual code PRs per vertical slice with tests and migration/rollback notes. Preserve GPERP `main` and Gateway `main` until changes pass review.
