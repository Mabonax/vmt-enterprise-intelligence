# Deployment Guide

- Use the Docker stack as the baseline runtime.
- Prefer PHP 8.3 and PostgreSQL 16 for target deployment.
- Store provider credentials and ERP secrets outside the repo.
- Run migrations before enabling queue workers on new environments.
- Seed only the administrator role set in production bootstrap flows.
