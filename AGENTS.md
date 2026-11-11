# Agent Constitution — imc-reengineer-backend

## Migrations

All migrations live in `migration-service/database/migrations/`.

**Create a new file for every schema change** — even when amending a table that an existing migration already touches. The staging server is partially live, so existing migration files are immutable; a new file is the only safe path.

Per-service `database/migrations/` folders are legacy scaffolding. Leave them untouched and write all new migrations in `migration-service` only.
