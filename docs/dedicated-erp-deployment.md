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

## Native Windows local acceptance profile

Use an existing native Ollama instance with XAMPP PHP/MySQL when Docker Desktop is unavailable. Keep the Gateway and ERP databases separate. The local commissioning profile uses loopback ports 8080 (Gateway), 8000 (clinic) and 11434 (Ollama). Do not start a Docker Ollama service alongside the native listener.

`scripts/start-local-native.ps1` starts missing local development listeners without replacing existing processes. Its default CPU mode limits Ollama to one loaded model, one parallel request and a 4096-token context. Supply `-ModelsPath` when existing model files are stored outside Ollama's default location. It preserves model files and does not configure production Windows services, workers or scheduler supervision.

```powershell
.\scripts\start-local-native.ps1 -ClinicPath C:\xampp\htdocs\gperp-clinic -ModelsPath $env:USERPROFILE
php artisan gateway:readiness
```

That model path was verified on the commissioning workstation; use the actual model directory elsewhere. Readiness checks model availability, not successful inference. Verify real chat and embeddings separately.

The 2026-10-10 workstation has NVIDIA driver 546.30, below Ollama's documented 550 minimum. CPU-only inference was verified for `llama3.2:3b` and `embeddinggemma` (768 dimensions). GPU inference requires a compatible driver; do not interpret CPU acceptance as GPU acceptance.

## Provisioning and context safeguards

Installation resolves the existing active organization/client before creating a tenant. Operator-created tenant slugs are supported. Multiple active matches, inactive-only client matches and inactive client tenants fail closed for operator resolution. Reuse does not emit or rotate credentials. Use the provisioning service or operator console to issue credentials, and send one-time output directly to an access-restricted server-side destination.

For connected clients, set `metadata.erp_system` to the approved ERP system identifier. Requests with a different identifier are rejected. Existing unbound clients retain their previous compatibility behavior; operators must bind them before relying on ERP identity checks.

`options.retrieve_knowledge=false` explicitly excludes Gateway knowledge retrieval. The context assembler respects that decision rather than silently repeating retrieval. GPERP's dedicated aggregate connector uses this option with `allow_actions=false`. Other consumers retain the default retrieval behavior.

Provider failures return a sanitized HTTP 502 while failed provider requests/traces are audited. Execution-plan display objectives are bounded to the MySQL column width; the validated full prompt stays in the request payload.

GPERP's opt-in `LocalGatewayLiveAcceptanceTest` exercises its authorized assistant route with a synthetic in-memory ERP database and real Gateway/Ollama HTTP transport. It is separate from authenticated browser acceptance and production readiness.
