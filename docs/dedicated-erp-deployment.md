# Dedicated ERP Deployment Contract

## Product boundary

VMT Enterprise AI Gateway is a separately deployed AI middleware service supplied and managed by VMT alongside a contracted VMT ERP. It is not an independently self-served SaaS marketplace.

- **ERP:** owns the user experience, business data, workflow, and domain authorization.
- **Gateway:** owns authenticated ERP capability APIs, approved context assembly, retrieval, provider routing, verification, audit, and technical observability.
- **Inference runtime:** Ollama by default, replaceable with an approved local or hosted model provider.
- **Storage:** the Gateway has its own operational database; it must not share ERP ownership of business records.

## Default posture

1. One dedicated customer-controlled deployment, with one or more explicitly provisioned ERP clients.
2. Installation and initial administrators are provisioned by VMT operators, not public registration.
3. Cloud inference and commercial-console features are off unless explicitly enabled through an approved deployment profile.
4. Keep tenant/client security boundaries even within a single dedicated deployment.
5. No subscription checkout or public organization signup is required for an ERP user.
6. ERP users access AI from their ERP; the Gateway UI is a technical operator surface.

## Environment configuration

- `VMT_DEPLOYMENT_MODE=dedicated`
- `VMT_PUBLIC_REGISTRATION=false`
- `VMT_COMMERCIAL_CONSOLE_ENABLED=false`
- `VMT_DEPLOYMENT_OPERATOR=VMT`

These are feature-gate defaults, not a full installation provisioning implementation. Restrict operator access at the infrastructure boundary and apply organization-/client-level authorization on every API and knowledge path.

## Per-customer installation acceptance

1. VMT provisions administrator access and connects the customer's ERP.
2. Gateway credentials are issued once, stored safely and rotated via approved workflow.
3. Private deployment networking prevents direct external exposure of inference and database services.
4. The configured chat and embedding models pass `gateway:readiness`.
5. A real ERP request completes through Gateway → inference → verification → audit → ERP response.
6. Negative tenant/client and knowledge-isolation tests pass.
7. Backups, restore procedures and operator support procedures have been verified.

## Retained legacy modules

Commercial, subscription and shared-tenant bounded contexts are retained for dependency compatibility and archival development history. They are not part of the default customer delivery or navigation. They must not be interpreted as an invitation for a public signup or shared SaaS tenant marketplace. A follow-up review should disable legacy routes/actions and reclassify relevant deployment/support functions without deleting dependent data.

## Operator CLI commissioning

After database migrations and the first VMT operator account have been securely initialized:

```bash
php artisan gateway:install --check
php artisan gateway:install --provision --organization-id=EXISTING_ORGANIZATION_UUID --erp-name=GPERP
php artisan gateway:install --verify
```

The provisioning operation requires a pre-existing approved organization UUID and creates a dedicated Gateway tenant and an ERP client, issuing an API key and secret only if that ERP client does not already exist. A repeated run reuses the existing client without printing or generating new credentials. Safeguard the one-time credential output: avoid CI logs, shell history and shared terminals. For credentials issued through the web UI, use the operator-only Connected Applications console.

Check is an environment and database preflight, while verify uses the configured live AI provider to check availability of the required chat and embedding models. Neither replaces an actual authenticated ERP-to-Gateway inference acceptance test. The VMT operator must also verify firewall rules, TLS, backup restoration, separate databases, queues and credential rotation before production handover.
