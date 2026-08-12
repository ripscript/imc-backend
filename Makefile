
SERVICES := master-service user-service content-service public-content-service file-service setting-service
COMPOSE  := docker compose -f docker-compose.local.yaml

# Default target
.DEFAULT_GOAL := help

.PHONY: help setup up down restart ps logs shell artisan tinker fresh test swagger debug nodebug

# ─────────────────────────────────────────────
# Help — auto-generated from ## comments
# ─────────────────────────────────────────────
help: ## Show available commands
	@printf "\n\033[1;36m IMC Backend — Dev Toolkit\033[0m\n\n"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) \
	  | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-18s\033[0m %s\n", $$1, $$2}'
	@printf "\n\033[90m  Examples:\033[0m\n"
	@printf "  \033[90mmake up svc=master-service\033[0m\n"
	@printf "  \033[90mmake logs svc=user-service\033[0m\n"
	@printf "  \033[90mmake artisan svc=master-service cmd=\"migrate:status\"\033[0m\n"
	@printf "  \033[90mmake shell svc=content-service\033[0m\n\n"

# ─────────────────────────────────────────────
# First-time setup
# ─────────────────────────────────────────────
setup: ## Copy .env.example → .env for all services (skips if .env exists)
	@echo "\033[1;33m⚙  Setting up environment files...\033[0m"
	@for svc in $(SERVICES); do \
	  if [ -f $$svc/.env.example ] && [ ! -f $$svc/.env ]; then \
	    cp $$svc/.env.example $$svc/.env; \
	    echo "  ✓ $$svc/.env created"; \
	  elif [ -f $$svc/.env ]; then \
	    echo "  – $$svc/.env already exists, skipping"; \
	  else \
	    echo "  ✗ $$svc/.env.example not found, skipping"; \
	  fi; \
	done
	@echo "\n\033[1;32m✓ Setup complete. Edit each .env, then run: make up\033[0m\n"

# ─────────────────────────────────────────────
# Docker Compose lifecycle
# ─────────────────────────────────────────────
up: ## Start services. Use svc=<name> to start a single service
ifdef svc
	$(COMPOSE) up -d $(svc)
else
	$(COMPOSE) up -d
endif

down: ## Stop and remove all containers
	$(COMPOSE) down

restart: ## Restart services. Use svc=<name> to restart a single service
ifdef svc
	$(COMPOSE) restart $(svc)
else
	$(COMPOSE) restart
endif

ps: ## Show running containers
	$(COMPOSE) ps

# ─────────────────────────────────────────────
# Xdebug — toggle without touching files
# ─────────────────────────────────────────────
debug: ## Enable Xdebug for a service. Required: svc=imc-<name>
# Docker Compose reads XDEBUG_MODE from the root .env (not the service .env).
# This target writes it there and restarts only the target container.
ifndef svc
	$(error Usage: make debug svc=imc-<service-name>  e.g. make debug svc=imc-content-service)
endif
	@if grep -q '^XDEBUG_MODE=' .env 2>/dev/null; then \
	  sed -i.bak 's/^XDEBUG_MODE=.*/XDEBUG_MODE=debug/' .env && rm -f .env.bak; \
	else \
	  echo 'XDEBUG_MODE=debug' >> .env; \
	fi
	@echo "\033[1;33m🐛 Xdebug ON → restarting $(svc)...\033[0m"
	$(COMPOSE) up -d --force-recreate $(svc)
	@echo "\033[1;32m✓ Set breakpoints in VSCode and attach '🐛 $(svc)' debugger\033[0m"

nodebug: ## Disable Xdebug for a service. Required: svc=imc-<name>
ifndef svc
	$(error Usage: make nodebug svc=imc-<service-name>)
endif
	@if grep -q '^XDEBUG_MODE=' .env 2>/dev/null; then \
	  sed -i.bak 's/^XDEBUG_MODE=.*/XDEBUG_MODE=off/' .env && rm -f .env.bak; \
	fi
	@echo "\033[1;33m🚫 Xdebug OFF → restarting $(svc)...\033[0m"
	$(COMPOSE) up -d --force-recreate $(svc)
	@echo "\033[1;32m✓ $(svc) running without Xdebug overhead\033[0m"

# ─────────────────────────────────────────────
# Logs
# ─────────────────────────────────────────────
logs: ## Tail logs. Use svc=<name> for a single service, or all by default
ifdef svc
	$(COMPOSE) logs -f --tail=100 $(svc)
else
	$(COMPOSE) logs -f --tail=50
endif

# ─────────────────────────────────────────────
# Shell & Artisan
# ─────────────────────────────────────────────
shell: ## Open a shell in a service container. Required: svc=<name>
ifndef svc
	$(error Usage: make shell svc=<service-name>)
endif
	$(COMPOSE) exec $(svc) sh

artisan: ## Run an artisan command. Required: svc=<name> cmd=<command>
ifndef svc
	$(error Usage: make artisan svc=<service-name> cmd="<artisan-command>")
endif
ifndef cmd
	$(error Usage: make artisan svc=<service-name> cmd="<artisan-command>")
endif
	$(COMPOSE) exec $(svc) php artisan $(cmd)

tinker: ## Launch artisan tinker. Required: svc=<name>
ifndef svc
	$(error Usage: make tinker svc=<service-name>)
endif
	$(COMPOSE) exec $(svc) php artisan tinker

fresh: ## Run migrate:fresh --seed. Required: svc=<name>
ifndef svc
	$(error Usage: make fresh svc=<service-name>)
endif
	$(COMPOSE) exec $(svc) php artisan migrate:fresh --seed

test: ## Run PHPUnit tests. Use svc=<name> for a single service, or all
ifdef svc
	$(COMPOSE) exec $(svc) php artisan test
else
	@for svc in $(SERVICES); do \
	  echo "\n\033[1;36m▶ Testing $$svc...\033[0m"; \
	  $(COMPOSE) exec $$svc php artisan test 2>/dev/null || true; \
	done
endif

# ─────────────────────────────────────────────
# Docs
# ─────────────────────────────────────────────
swagger: ## Generate OpenAPI JSON specs for all services
	@echo "\033[1;33m📄 Generating OpenAPI specs...\033[0m"
	@mkdir -p docs-service/storage/app/specs
	@for svc in $(SERVICES); do \
	  if [ -f $$svc/artisan ]; then \
	    echo "  → $$svc"; \
	    cd $$svc && php artisan scramble:export --path=public/specs/api.json && cd ..; \
	    cp $$svc/public/specs/api.json docs-service/storage/app/specs/$${svc}.json; \
	  fi; \
	done
	@echo "\033[1;32m✓ Specs in docs-service/storage/app/specs/\033[0m\n"
