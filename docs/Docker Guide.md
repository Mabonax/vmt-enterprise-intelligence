# Docker Guide

VIP ships with services for:

- `app`: PHP-FPM 8.3 runtime
- `nginx`: web entrypoint
- `postgres`: primary database
- `redis`: cache and queue backend
- `queue`: worker process
- `scheduler`: Laravel scheduler loop
- `workspace`: utility shell for Composer and Node operations

Run `docker compose up --build` to start the stack.
