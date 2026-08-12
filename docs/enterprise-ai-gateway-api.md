# Enterprise AI Gateway API

Phase 12 introduces the first ERP-facing gateway contract. The gateway is now the only supported AI entry point for VMT ERP systems.

## Authentication

- Every ERP request must include `X-ERP-System: <system_key>`
- Every ERP request must include `X-ERP-Key: <shared secret>`
- `Authorization: Bearer <shared secret>` is also accepted
- ERP credentials are validated against `connected_erps` and `erp_api_keys`

## Public endpoints

- `GET /api/gateway/v1/health`
- `POST /api/gateway/v1/chat`
- `POST /api/gateway/v1/summarise`
- `POST /api/gateway/v1/report`
- `POST /api/gateway/v1/translate`
- `POST /api/gateway/v1/classify`
- `POST /api/gateway/v1/search`
- `POST /api/gateway/v1/action`
- `GET /api/gateway/v1/requests/{gatewayRequest}`

## Request envelope

```json
{
  "organization_id": "00000000-0000-0000-0000-000000000001",
  "erp_system": "clinic-erp",
  "correlation_id": "erp-req-001",
  "actor": {
    "id": "user-42",
    "roles": ["clinician"],
    "permissions": ["patients.view", "notes.summarise"]
  },
  "subject": {
    "type": "patient_record",
    "id": "pat-9001"
  },
  "prompt": "Summarise the record for handover.",
  "context": {
    "module": "clinical"
  },
  "knowledge_references": ["clinical-policies"],
  "options": {
    "allow_actions": false
  }
}
```

## Action requests

The `action` capability can execute approved tools through the existing tool framework:

```json
{
  "organization_id": "00000000-0000-0000-0000-000000000001",
  "erp_system": "clinic-erp",
  "correlation_id": "erp-req-002",
  "actor": {
    "id": "user-42"
  },
  "prompt": "Run the approved gateway action set.",
  "actions": [
    {
      "tool": "current_datetime",
      "payload": {}
    }
  ],
  "options": {
    "allow_actions": true
  }
}
```

## Response envelope

```json
{
  "request_id": "req-123",
  "trace_id": "trace-123",
  "status": "completed",
  "capability": "chat",
  "output": {
    "text": "..."
  },
  "citations": [],
  "provider": {
    "key": "ollama",
    "model": "runtime-placeholder"
  },
  "verification": {
    "passed": true,
    "confidence_score": 0.96
  },
  "audit": {
    "logged": true
  }
}
```
