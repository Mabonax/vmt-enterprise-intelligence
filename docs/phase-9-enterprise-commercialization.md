# Phase 9 Enterprise Commercialization

## What Was Added
- New bounded context: `app/Domains/Intelligence/Commercial`
- Enterprise package catalog with features, entitlements, limits, module access, pricing rules, and upgrade paths
- Tenant provisioning workflow, deployment planning, branding, security, residency, and workspace setup
- Subscription, usage metering, quota enforcement, invoice payload generation, and billing readiness foundations
- Proposal pipeline, approval flow, handover payloads, deployment runbooks, support tickets, SLA posture, maintenance windows, and release readiness checks
- Commercial workspace pages inside the existing Intelligence VIP shell
- Commercial API endpoints under `/api/intelligence/commercial/*`

## Database Tables
- `intelligence_packages`, `package_features`, `package_limits`, `package_entitlements`, `package_module_accesses`, `package_pricing_rules`, `package_upgrade_paths`
- `intelligence_tenants`, `tenant_workspaces`, `tenant_provisioning_requests`, `tenant_provisioning_checklists`, `tenant_environments`, `tenant_domains`, `tenant_branding_profiles`, `tenant_security_profiles`, `tenant_data_residency_profiles`, `tenant_deployment_profiles`
- `billing_accounts`, `billing_contacts`, `payment_instructions`, `intelligence_subscriptions`, `subscription_invoices`, `subscription_invoice_lines`, `usage_meters`, `usage_ledger_entries`, `usage_quotas`, `usage_overages`
- `intelligence_proposals`, `proposal_package_lines`, `proposal_deployment_lines`, `proposal_service_lines`, `proposal_assumptions`, `proposal_risks`, `proposal_approvals`, `commercial_handovers`
- `deployment_runbooks`, `deployment_steps`, `deployment_evidences`, `support_plans`, `support_tickets`, `support_slas`, `support_escalations`, `maintenance_windows`, `release_readiness_checks`

## Services
- Provisioning: `TenantProvisioningService`, `TenantWorkspaceBuilder`, `TenantSecurityConfigurator`, `TenantBrandingService`, `TenantDeploymentPlanner`, `TenantReadinessService`
- Billing and usage: `SubscriptionService`, `UsageMeteringService`, `InvoiceGenerationService`, `QuotaEnforcementService`, `BillingReadinessService`
- Proposals: `IntelligenceProposalBuilder`, `PricingEstimator`, `CommercialRiskAnalyzer`, `ProposalApprovalService`, `CommercialHandoverService`
- Deployment and support: `DeploymentRunbookService`, `ReleaseReadinessService`, `SupportTicketService`, `SlaMonitor`, `MaintenancePlanner`

## Routes
- Web workspace pages:
  - `/intelligence/commercial/packages`
  - `/intelligence/commercial/tenants`
  - `/intelligence/commercial/provisioning`
  - `/intelligence/commercial/subscriptions`
  - `/intelligence/commercial/usage`
  - `/intelligence/commercial/billing`
  - `/intelligence/commercial/proposals`
  - `/intelligence/commercial/deployments`
  - `/intelligence/commercial/support`
  - `/intelligence/commercial/readiness`
  - `/intelligence/commercial/settings`
- API:
  - `/api/intelligence/commercial/packages`
  - `/api/intelligence/commercial/tenants`
  - `/api/intelligence/commercial/provisioning`
  - `/api/intelligence/commercial/subscriptions`
  - `/api/intelligence/commercial/usage`
  - `/api/intelligence/commercial/billing`
  - `/api/intelligence/commercial/proposals`
  - `/api/intelligence/commercial/deployments`
  - `/api/intelligence/commercial/support`
  - `/api/intelligence/commercial/readiness`

## Pages
- Commercial packages and pricing catalog
- Tenant portfolio and provisioning queue
- Active subscriptions, usage, quotas, overages, and billing readiness
- Proposal pipeline and deployment runbooks
- Support operations and release readiness

## Usage And Billing Model
- Usage meters track agent executions, token consumption, storage, and connector calls
- Quotas warn as usage nears the configured threshold
- Overage rows are recorded when consumption crosses the package limit
- Hard blocking only appears when a meter is marked as a hard limit
- Invoice generation produces structured draft payloads and line items without a payment gateway dependency

## Tenant Provisioning Workflow
- `draft`
- `submitted`
- `reviewed`
- `approved`
- `provisioning`
- `active`
- `suspended`
- `retired`

## Deployment Modes
- `shared_saas`
- `dedicated_saas`
- `on_premise`
- `private_cloud`
- `sovereign_self_hosted`

## Commercial Readiness Checklist
- Package selected and pricing rule available
- Tenant provisioning request approved
- Workspace created
- Security profile applied
- Branding profile applied
- Deployment profile prepared
- Runbook created
- Billing account ready
- Support plan or ticket flow available

## Test Commands
```powershell
php artisan test --filter PhaseNine
php artisan test --filter Commercial
vendor\bin\phpunit --filter PhaseNine
```

## Known Caveats
- Billing remains gateway-free by design in this phase
- Proposal payloads are structured for future document generation, not PDF output yet
- Commercial workspace pages reuse the generic Intelligence workspace renderer, so they are data-heavy and intentionally additive rather than fully custom UI screens
