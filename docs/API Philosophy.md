# API Philosophy

VIP is API-first and ERP-safe.

- Every ERP integration should be explicit and authenticated.
- Contracts should remain stable across provider swaps.
- Request/response payloads should be transport-agnostic where possible.
- Long-running work should favor async acknowledgement plus job orchestration.
