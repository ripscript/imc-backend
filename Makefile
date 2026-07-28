SERVICES := master-service user-service content-service public-content-service file-service setting-service

.PHONY: swagger-auto help

swagger-auto: ## Generate OpenAPI JSON specs for all services
	@echo "Generating OpenAPI specs..."
	@mkdir -p docs-service/storage/app/specs
	@for svc in $(SERVICES); do \
		echo "  → $$svc"; \
		cd $$svc && php artisan scramble:export --path=public/specs/api.json && cd ..; \
		cp $$svc/public/specs/api.json docs-service/storage/app/specs/$${svc}.json; \
	done
	@echo "Done! Specs in docs-service/storage/app/specs/"

help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'
