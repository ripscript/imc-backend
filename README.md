# IMC Backend

Multi-service Laravel backend monorepo. Services communicate through a shared Nginx reverse proxy.

## Services

| Container | Path prefix | Port (internal) |
|---|---|---|
| `imc-master-service` | `/master-service/` | 8000 |
| `imc-user-service` | `/user-service/` | 8000 |
| `imc-content-service` | `/content-service/` | 8000 |
| `imc-public-content-service` | `/public-content-service/` | 8000 |
| `imc-file-service` | `/file-service/` | 8000 |
| `imc-setting-service` | `/setting-service/` | 8000 |

All services are exposed through Nginx on `http://localhost:4076`.

---

## Quick Start

### 1. Clone & setup environment files

```sh
git clone <repo-url>
cd imc-reengineer-backend
make setup      # copies .env.example → .env for every service
```

Edit each service's `.env` with your DB credentials before continuing.

### 2. Start everything

```sh
make up
```

Nginx will only start routing after all services pass their health checks (up to ~30s on first run).

---

## Daily Dev Commands

```sh
make help                                     # show all commands

make up                                       # start all services
make up svc=imc-master-service                # start one service
make down                                     # stop everything
make restart svc=imc-user-service             # restart one service
make ps                                       # show running containers

make logs                                     # tail all logs
make logs svc=imc-content-service             # tail one service

make shell svc=imc-master-service             # open shell in container
make artisan svc=imc-master-service cmd="migrate:status"
make tinker svc=imc-user-service
make fresh  svc=imc-master-service            # migrate:fresh --seed

make test                                     # run all tests
make test svc=imc-master-service              # run tests for one service

make swagger                                  # regenerate OpenAPI specs
```

---

## Debugging with Xdebug

Xdebug is **off by default** — zero overhead during normal development.

### Enable for a session (without restart)

Set the `XDEBUG_TRIGGER` cookie or header in your HTTP client:
- **curl**: `curl -H "Cookie: XDEBUG_TRIGGER=1" http://localhost:4076/master-service/...`
- **Postman**: Add cookie `XDEBUG_TRIGGER=1`
- **Browser**: Install [Xdebug Helper](https://chromewebstore.google.com/detail/xdebug-helper/eadndfjplgieldjbigjakmdgkmoaaaoc)

### Enable globally (requires container restart)

Add `XDEBUG_MODE=debug` to the service's `.env`, then:

```sh
make restart svc=imc-master-service
```

### VSCode setup

1. Install [PHP Debug extension](https://marketplace.visualstudio.com/items?itemName=xdebug.php-debug)
2. Open **Run & Debug** (`⇧⌘D`)
3. Select the service you want to debug (e.g. `🐛 master-service`)
4. Press **F5** — the debugger listens on port 9003
5. Trigger a request — breakpoints will be hit

> **Note**: Only one debug session can be active at a time on port 9003. Stop the current session before starting a new one for a different service.

---

## Project Structure

```
imc-reengineer-backend/
├── Makefile                     # Dev toolkit — all commands
├── docker-compose.yaml          # All services + nginx
├── nginx/
│   └── app.config.dev           # Route prefix → service mapping
├── master-service/
│   ├── docker/
│   │   ├── php/xdebug.ini       # Xdebug config (off by default)
│   │   └── scripts/start.sh     # Container entrypoint
│   └── ...
└── [user|content|public-content|file|setting]-service/
    └── (same structure)
```

---

## Generating API Docs

```sh
make swagger
```

Exports OpenAPI specs for all services into `docs-service/storage/app/specs/`.
