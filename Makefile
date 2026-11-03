# DirectChannel — automação do ambiente de desenvolvimento
# Uso: `make dev` sobe tudo; `make help` lista os comandos.

COMPOSE := docker compose -f infra/compose.yaml
RUN     := $(COMPOSE) run --rm --no-deps api
EXEC    := $(COMPOSE) exec api

# O container da API roda com o seu usuário (evita arquivos de root em backend/)
export UID := $(shell id -u)
export GID := $(shell id -g)

API_PORT := $(or $(shell grep -oP '^API_PORT=\K.*' infra/.env 2>/dev/null),8010)

.DEFAULT_GOAL := help
.PHONY: help dev setup env build deps key up down restart logs ps migrate fresh \
        test tinker shell artisan composer db mobile mobile-deps clean

help: ## Lista os comandos disponíveis
	@grep -E '^[a-zA-Z_-]+:.*?## ' $(MAKEFILE_LIST) | \
		awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-12s\033[0m %s\n", $$1, $$2}'

dev: setup up migrate ## Prepara (se preciso), sobe API + banco e roda migrations
	@echo ""
	@echo "API em http://localhost:$(API_PORT) — app mobile: make mobile"

setup: env build deps key ## Primeira instalação: .env, imagem, dependências e APP_KEY

env: ## Cria infra/.env, backend/.env e mobile/.env se não existirem
	@test -f infra/.env || { \
		echo "DB_PASSWORD=$$(openssl rand -hex 16)" > infra/.env; \
		echo "infra/.env criado com senha aleatória"; }
	@test -f backend/.env || { cp backend/.env.example backend/.env; echo "backend/.env criado"; }
	@# artisan serve lê o backend/.env, então a senha do banco precisa estar lá também
	@sed -i "s|^DB_PASSWORD=.*|$$(grep '^DB_PASSWORD=' infra/.env)|" backend/.env
	@test -f mobile/.env || { \
		IP=$$(hostname -I | awk '{print $$1}'); \
		sed "s/SEU_IP_LOCAL/$$IP/" mobile/.env.example > mobile/.env; \
		echo "mobile/.env criado apontando para $$IP"; }

build: ## Constrói a imagem da API
	$(COMPOSE) build api

deps: ## Instala dependências do backend (composer) via container
	@test -d backend/vendor || $(RUN) composer install --no-interaction

key: ## Gera APP_KEY no backend/.env se estiver vazio
	@grep -q '^APP_KEY=base64:' backend/.env || $(RUN) php artisan key:generate

up: ## Sobe API e banco em segundo plano
	$(COMPOSE) up -d --wait

down: ## Para os containers
	$(COMPOSE) down

restart: down up ## Reinicia os containers

logs: ## Acompanha os logs (make logs s=postgres para um serviço)
	$(COMPOSE) logs -f $(s)

ps: ## Mostra o estado dos containers
	$(COMPOSE) ps

migrate: ## Roda as migrations
	$(EXEC) php artisan migrate --force

fresh: ## Recria o banco do zero com seeders
	$(EXEC) php artisan migrate:fresh --seed

test: ## Roda os testes do backend
	$(EXEC) php artisan test

tinker: ## Abre o Tinker
	$(EXEC) php artisan tinker

shell: ## Abre um shell no container da API
	$(EXEC) bash

artisan: ## Roda um comando artisan (make artisan c="route:list")
	$(EXEC) php artisan $(c)

composer: ## Roda um comando composer (make composer c="require pacote")
	$(EXEC) composer $(c)

db: ## Abre o psql no banco
	$(COMPOSE) exec postgres psql -U directchannel -d directchannel

mobile-deps:
	@test -d mobile/node_modules || (cd mobile && npm install)

mobile: env mobile-deps ## Inicia o Expo (app mobile)
	cd mobile && npx expo start

clean: ## Remove containers E o volume do banco (apaga os dados!)
	$(COMPOSE) down -v
