# WCM dev helpers — thin wrappers around docker compose.
# Usage: `make setup` once, then `make up`.
.DEFAULT_GOAL := help
DC := docker compose
APP := $(DC) run --rm wcm-app
NODE := docker run --rm -v "$(CURDIR)":/app -w /app node:22-alpine

.PHONY: help
help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | \
		awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-14s\033[0m %s\n", $$1, $$2}'

.PHONY: build
build: ## Build the app image
	$(DC) build

.PHONY: setup
setup: build ## First-time setup: deps, ziggy routes, assets
	$(DC) up -d --wait wcm-mysql
	$(APP) composer install --no-interaction
	$(MAKE) assets
	@echo "\nSetup complete. Run 'make up' — app at http://localhost:8123"

.PHONY: assets
assets: ## Regenerate ziggy.js (gitignored, app.js imports it) and build the frontend
	$(APP) php artisan ziggy:generate
	$(NODE) sh -lc "npm ci && npm run build"

.PHONY: up
up: ## Start the stack
	$(DC) up -d

.PHONY: down
down: ## Stop the stack (keeps volumes)
	$(DC) down

.PHONY: logs
logs: ## Tail all logs
	$(DC) logs -f

.PHONY: sh
sh: ## Shell into the app container
	$(DC) run --rm wcm-app bash
