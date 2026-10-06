# Deployment Guide

## Baseline deployment

Use the Docker stack as the default deployment baseline for the VMT Enterprise AI Gateway.

The baseline stack includes:

- Laravel application
- Nginx
- PostgreSQL 16
- Redis 7
- queue worker
- scheduler
- Ollama local inference runtime
- persistent Ollama model storage
- an initialization container that ensures the default chat and embedding models are present

## Local AI defaults

The default materialization profile is:

- provider: `ollama`
- chat model: `llama3.2:3b`
- embedding model: `embeddinggemma`
- cloud providers: disabled
- scaffold/stub providers: disabled
- provider allowlist: `ollama`

For host-based development, the default Ollama endpoint is:

```text
http://localhost:11434/api
```

Inside Docker, Compose overrides it with:

```text
http://ollama:11434/api
```

Do not use `localhost` for Ollama from inside the Laravel container.

## Bootstrap

```bash
cp .env.example .env
docker compose up --build
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan gateway:readiness
```

The readiness command exits with status `0` only when:

- the configured provider is registered
- the provider is healthy
- the provider is eligible for production traffic
- the configured chat model is available
- the configured embedding model is available when knowledge retrieval is enabled

The same state is exposed through:

```text
GET /api/gateway/v1/health
```

## Secrets and production settings

Before a shared or production deployment:

- generate a unique `APP_KEY`
- replace the default administrator password
- store ERP credentials and provider credentials outside source control
- set `APP_DEBUG=false`
- use TLS at the ingress/reverse proxy
- keep `AI_CLOUD_PROVIDERS_ENABLED=false` unless egress has been explicitly approved
- keep `AI_ALLOW_STUB_PROVIDERS=false`
- keep the provider allowlist as narrow as the deployment requires
- rotate gateway client credentials according to the customer security policy

## Database and workers

Run migrations before enabling production traffic.

Queue workers and the scheduler must use the same provider configuration as the web application. The Docker stack already supplies the same Ollama runtime values to these services.

## Production acceptance gate

A deployment is not production-ready merely because containers are running.

Before enabling an ERP client, prove all of the following:

1. `php artisan gateway:readiness` exits successfully.
2. A gateway client can authenticate.
3. An unauthorized client is rejected.
4. A cross-tenant request is rejected.
5. A real chat request reaches Ollama and returns a model response.
6. Knowledge retrieval returns only tenant-authorized context.
7. An approved tool action can execute.
8. A forbidden action is rejected.
9. The request produces an execution trace, audit record, security event, and usage record.
10. No ERP contains provider credentials or direct model-provider integration.
