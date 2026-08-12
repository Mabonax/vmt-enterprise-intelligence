# Phase 6 Upgrade Guide

## Apply Migrations

```bash
php artisan migrate
```

This adds the enterprise knowledge platform tables and preserves Phase 1-5 schema.

## New Routes

- Web knowledge workspace routes under `/intelligence/knowledge/*`
- API routes under `/api/knowledge/*`

## Scheduler

Phase 6 registers scheduled jobs in `routes/console.php` for cleanup, reindexing, health, consolidation, learning, and verification.

## Runtime Behavior

- Knowledge retrieval is controlled by `config/intelligence.php` under `knowledge.retrieval`.
- Prompt assembly remains backward compatible because retrieval results are folded into runtime context rather than changing prompt message contracts.
- Agent execution now records learning-cycle data after verification.
