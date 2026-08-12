# Development Guide

## Local

1. Install Composer and Node dependencies.
2. Configure `.env`.
3. Run `php artisan migrate --seed`.
4. Run `npm run dev` or `npm run build`.

## Suggested next steps

1. Finalize provider contract DTOs.
2. Add repository implementations per domain.
3. Introduce request validation and policy layers for platform modules.
4. Implement connection-safe outbound HTTP clients and telemetry.
