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
	@echo "\nSetup complete. Run 'make up' — app at http://localhost:8123, inbox at http://localhost:8025"

.PHONY: assets
assets: ## Regenerate ziggy.js (gitignored, app.js imports it) and build the frontend
	$(APP) php artisan ziggy:generate
	$(NODE) sh -lc "npm ci && npm run build"

.PHONY: og
og: ## Re-render the sharing cards from resources/og
	node resources/og/render.mjs

.PHONY: up
up: ## Start the stack
	$(DC) up -d
	@echo "app: http://localhost:8123   ·   inbox: http://localhost:8025"

.PHONY: down
down: ## Stop the stack (keeps volumes)
	$(DC) down

.PHONY: logs
logs: ## Tail all logs
	$(DC) logs -f

.PHONY: sh
sh: ## Shell into the app container
	$(DC) run --rm wcm-app bash

.PHONY: admin
admin: ## Create a local login for the backoffice: make admin EMAIL=you@example.com PASSWORD=secret
	@test -n "$(EMAIL)" || (echo "Usage: make admin EMAIL=you@example.com PASSWORD=your-password"; exit 1)
	@test -n "$(PASSWORD)" || (echo "Usage: make admin EMAIL=you@example.com PASSWORD=your-password"; exit 1)
	@$(DC) exec -T wcm-app php artisan admin:create "$(EMAIL)" "$(PASSWORD)"

.PHONY: registrations
registrations: ## Show the registrations recorded so far
	@$(DC) exec -T wcm-mysql mysql -uroot -psecret -t -e "\
		select id, name, email, locale, days, workshop_interest as workshop, \
		if(confirmed_at is null, 'pending', 'confirmed') as status, sent_count \
		from wcm_2026.registrations order by id;" 2>/dev/null

.PHONY: registrations-reset
registrations-reset: ## Empty registrations and the dev inbox (contributions are kept)
	@$(DC) exec -T wcm-mysql mysql -uroot -psecret -e "update wcm_2026.contributions set registration_id = null; delete from wcm_2026.registrations;" 2>/dev/null
	@curl -s -X DELETE http://localhost:8025/api/v1/messages >/dev/null || true
	@echo "Registrations and inbox cleared. Contributions kept — run 'make contributions' to see them."

.PHONY: contributions-reconcile
contributions-reconcile: ## Write in any paid Stripe session the webhook missed
	@$(DC) exec -T wcm-app php artisan contributions:reconcile

.PHONY: contributions
contributions: ## Show the contributions recorded by the Stripe webhook
	@$(DC) exec -T wcm-mysql mysql -uroot -psecret -t -e "\
		select c.id, c.session_id, format(c.amount/100, 2) as amount, upper(c.currency) as ccy, \
		c.status, coalesce(r.name, '(no registration)') as who, c.paid_at \
		from wcm_2026.contributions c left join wcm_2026.registrations r on r.id = c.registration_id \
		order by c.id;" 2>/dev/null
