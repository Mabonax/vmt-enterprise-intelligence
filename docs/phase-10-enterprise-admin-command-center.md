# Phase 10 Enterprise Admin Command Center

## Purpose

Phase 10 adds an internal enterprise admin console on top of the existing Intelligence, Operations, Knowledge, Agents, and Commercial phases. The goal is to give VMT operators, administrators, and enterprise owners one command center for monitoring posture, triaging alerts, managing safe internal actions, reviewing audit activity, and tracking readiness.

## Architecture

- New bounded context: `App\Domains\Intelligence\AdminConsole`
- Additive orchestration only: Phase 10 aggregates existing domain data and does not replace Commercial, Operations, Agents, or Knowledge services
- Custom Inertia pages are introduced for admin-console views where the generic workspace renderer is too limited
- Policies and Spatie permissions gate viewing, actions, health, audit, and readiness surfaces

## New Tables

- `admin_console_dashboards`
- `admin_console_widgets`
- `admin_console_alerts`
- `admin_console_actions`
- `admin_console_audit_events`
- `admin_console_health_snapshots`

## Services

- `AdminConsoleDashboardService`: builds dashboard payloads by audience
- `AdminConsoleMetricsService`: aggregates cross-domain operational counts
- `AdminConsoleAlertService`: creates and manages command center alerts with duplicate-open prevention
- `AdminConsoleActionService`: requests, approves, and safely executes internal actions
- `AdminConsoleHealthService`: computes signals, score, overall status, and recommendations
- `AdminConsoleAuditService`: records and queries audit timeline entries
- `AdminConsoleNavigationService`: contributes Admin Console entries into VIP navigation
- `AdminConsoleReadinessService`: reports readiness checks, blockers, and next actions

## Routes

### Web

- `/intelligence/admin-console`
- `/intelligence/admin-console/executive`
- `/intelligence/admin-console/operations`
- `/intelligence/admin-console/commercial`
- `/intelligence/admin-console/technical`
- `/intelligence/admin-console/support`
- `/intelligence/admin-console/compliance`
- `/intelligence/admin-console/alerts`
- `/intelligence/admin-console/actions`
- `/intelligence/admin-console/audit`
- `/intelligence/admin-console/health`
- `/intelligence/admin-console/readiness`

### API

- `GET /api/intelligence/admin-console/dashboard`
- `GET /api/intelligence/admin-console/metrics`
- `GET /api/intelligence/admin-console/alerts`
- `GET /api/intelligence/admin-console/actions`
- `GET /api/intelligence/admin-console/health`
- `GET /api/intelligence/admin-console/readiness`
- `GET /api/intelligence/admin-console/audit`
- `POST /api/intelligence/admin-console/actions`

## Frontend Pages

- `Index`
- `Executive`
- `Operations`
- `Commercial`
- `Technical`
- `Support`
- `Compliance`
- `Alerts`
- `Actions`
- `Audit`
- `Health`
- `Readiness`

Each page stays inside the existing VIP shell and uses the black-and-white enterprise presentation style already established in the project.

## Operational Workflows

- Alert triage: acknowledge, resolve, or dismiss alerts
- Action queue: request, approve, and execute safe internal actions
- Audit trail: every state-changing alert and action transition writes an admin-console audit event
- Health snapshots: cross-domain signals are collapsed into a platform score and recommendation set
- Readiness review: commercial, tenant, deployment, support, compliance, and monitoring checks are surfaced as blockers and next actions

## Authorization

Permissions added:

- `intelligence.admin_console.view`
- `intelligence.admin_console.manage`
- `intelligence.admin_console.alerts.manage`
- `intelligence.admin_console.actions.manage`
- `intelligence.admin_console.audit.view`
- `intelligence.admin_console.health.view`
- `intelligence.admin_console.readiness.view`

Administrators are also accepted through role-based access.

## Testing Notes

Recommended verification sequence:

- `php artisan test --filter PhaseTen`
- `php artisan test --filter AdminConsole`
- `php artisan test --filter NavigationRenderingTest`
- `php artisan route:list --name=intelligence.admin-console`
- `php artisan route:list --path=api/intelligence/admin-console`

## Caveats

- Billing gateway execution remains out of scope
- PDF generation remains out of scope
- Safe action execution currently focuses on internal no-op or health-refresh style actions only
- The admin console is an internal operator surface, not a customer-facing tenant portal

## Next Recommended Phase

Phase 11 should focus on tenant-facing enterprise operations: customer-visible tenant control rooms, guided onboarding workspaces, richer deployment evidence collection, and externalized support and readiness workflows with stable API contracts.
