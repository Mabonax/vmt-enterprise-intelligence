# Phase 5 Upgrade Guide

## Database

Run the additive migration set:

```bash
php artisan migrate
```

Phase 5 introduces new enterprise connector platform tables. Existing Phase 1-4 tables are unchanged.

## Boot-Time Discovery

- `IntelligenceServiceProvider` now synchronizes both the legacy `ai_tools` catalog and the new `enterprise_tools` registry.
- Connector registrations and health rows are also synchronized at boot once the Phase 5 tables exist.

## Execution Behavior

- `ToolExecutor` now prefers enterprise tool records when a matching slug exists.
- Legacy runtime tools still work because attribute-discovered tools are mirrored into the enterprise registry and can still execute through their handler class.
- Connector-backed tools capture usage, costs, health, execution streams, and replay payloads automatically.

## Admin Workspace

New Intelligence routes:

- `/intelligence/marketplace`
- `/intelligence/connectors`
- `/intelligence/credentials`
- `/intelligence/analytics`
- `/intelligence/health`
- `/intelligence/execution-graph`
- `/intelligence/execution-history`
- `/intelligence/testing`
- `/intelligence/sdk`
- `/intelligence/costs`

## Suggested Next Extensions

- Bind real external REST, GraphQL, webhook, email, and calendar integrations behind vault-backed credentials.
- Add queue-resume hooks so long-running enterprise tools can resume workflows automatically.
- Generate SDK artifacts from persisted manifests instead of placeholder export records.
- Add broadcast/event streaming for live frontend execution progress subscriptions.
