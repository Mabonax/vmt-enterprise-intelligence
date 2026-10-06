# VMT Enterprise AI Gateway

VMT Enterprise AI Gateway is the centralized, provider-agnostic AI integration layer for VMT ERP systems.

The product boundary is deliberate:

- ERP systems own business workflows and business data.
- The gateway owns AI authentication, authorization, context assembly, retrieval, tools, routing, verification, audit, observability, and provider abstraction.
- AI runtimes such as Ollama perform inference.
- Local AI is the default deployment model.
- Cloud providers remain optional and disabled by default.

## Current materialization status

The gateway is no longer only architectural scaffolding. The current implementation includes:

- ERP-facing gateway capability endpoints
- API key, HMAC, and JWT gateway authentication
- replay protection, tenant isolation, scopes, provider/model authorization, and usage enforcement
- request correlation, execution plans, execution traces, audit logs, security events, and usage records
- knowledge retrieval, tools, approvals, and verification
- a production Ollama adapter for chat, streaming, embeddings, model discovery, and health checks
- an operational health endpoint at `GET /api/gateway/v1/health`
- a CLI readiness check via `php artisan gateway:readiness`

Scaffold-only providers are blocked from production gateway traffic by default. Ollama is the production local provider in the current materialization baseline.

See [Enterprise AI Gateway Specification](docs/enterprise-ai-gateway-specification.md), [Architecture](docs/Architecture.md), [Domain Overview](docs/Domain%20Overview.md), and [Roadmap](docs/Roadmap.md).

## Default local runtime

The default local materialization profile is:

- Provider: `ollama`
- Chat model: `llama3.2:3b`
- Embedding model: `embeddinggemma`
- Cloud providers: disabled
- Stub providers: disabled

All values remain configurable through environment variables.

## Docker quick start

The Docker stack now includes PostgreSQL, Redis, Laravel, queue worker, scheduler, Nginx, Ollama, and an Ollama initialization container that pulls the required local models.

```bash
cp .env.example .env
docker compose up --build
```

The application is exposed at:

```text
http://localhost:8080
```

The Ollama API is exposed at:

```text
http://localhost:11434
```

After the stack starts, verify readiness:

```bash
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan gateway:readiness
```

A readiness exit code of `0` means the configured provider is healthy, eligible for production traffic, and both the configured chat and embedding models are installed.

You can also inspect:

```text
GET http://localhost:8080/api/gateway/v1/health
```

## Non-Docker local development

If Ollama is already installed on the host:

```bash
ollama pull llama3.2:3b
ollama pull embeddinggemma

composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
php artisan gateway:readiness
php artisan serve
```

The default `.env.example` uses `http://localhost:11434/api` for non-containerized Ollama. Docker overrides this with `http://ollama:11434/api`.

## Default administrator

- Email: `admin@vip.local`
- Password: `password`

Override these with `VIP_ADMIN_NAME`, `VIP_ADMIN_EMAIL`, and `VIP_ADMIN_PASSWORD` before any shared or production deployment.

## Gateway API

Primary capability endpoints:

- `POST /api/gateway/v1/chat`
- `POST /api/gateway/v1/summarise`
- `POST /api/gateway/v1/report`
- `POST /api/gateway/v1/translate`
- `POST /api/gateway/v1/classify`
- `POST /api/gateway/v1/search`
- `POST /api/gateway/v1/action`

The remaining productization priority is to integrate the first real VMT ERP through this contract and prove a non-mocked end-to-end request against the local runtime.
