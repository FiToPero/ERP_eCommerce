# Makefile
# Comandos útiles para desarrollo y producción

.PHONY: help dev prod start stop restart logs build clean test migrate seed fresh deploy health

# Variables
COMPOSE_DEV = docker compose -f docker-compose.base.yml -f docker-compose.dev.yml
COMPOSE_PROD = docker compose -f docker-compose.base.yml -f docker-compose.prod.yml
PHP_EXEC = docker compose exec -w /var/www/html/Laravel_app php

help: ## Mostrar esta ayuda
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

##@ Desarrollo

dev: ## Iniciar ambiente de desarrollo
	$(COMPOSE_DEV) --profile dev up -d
	@echo "✅ Ambiente de desarrollo iniciado"
	@echo "Laravel: http://localhost:8090"
	@echo "Vue.js: http://localhost:8081"
	@echo "Mailpit: http://localhost:8025"

dev-build: ## Construir e iniciar desarrollo
	$(COMPOSE_DEV) --profile dev build --no-cache
	$(COMPOSE_DEV) --profile dev up -d

dev-stop: ## Detener desarrollo
	$(COMPOSE_DEV) down

dev-logs: ## Ver logs de desarrollo
	$(COMPOSE_DEV) logs -f

##@ Producción

prod: ## Iniciar ambiente de producción
	$(COMPOSE_PROD) up -d
	@echo "✅ Ambiente de producción iniciado"

prod-build: ## Construir e iniciar producción
	$(COMPOSE_PROD) build --no-cache
	$(COMPOSE_PROD) up -d

prod-stop: ## Detener producción
	$(COMPOSE_PROD) down

prod-logs: ## Ver logs de producción
	$(COMPOSE_PROD) logs -f

##@ Laravel

install: ## Instalar dependencias PHP
	$(PHP_EXEC) composer install

migrate: ## Ejecutar migraciones
	$(PHP_EXEC) php artisan migrate

migrate-fresh: ## Reset database y migrar
	$(PHP_EXEC) php artisan migrate:fresh

seed: ## Ejecutar seeders
	$(PHP_EXEC) php artisan db:seed

fresh: ## Reset, migrar y seed
	$(PHP_EXEC) php artisan migrate:fresh --seed

test: ## Ejecutar tests
	$(PHP_EXEC) php artisan test

cache-clear: ## Limpiar todos los caches
	$(PHP_EXEC) php artisan cache:clear
	$(PHP_EXEC) php artisan config:clear
	$(PHP_EXEC) php artisan route:clear
	$(PHP_EXEC) php artisan view:clear

cache: ## Cachear configuraciones
	$(PHP_EXEC) php artisan config:cache
	$(PHP_EXEC) php artisan route:cache
	$(PHP_EXEC) php artisan view:cache

optimize: ## Optimizar aplicación
	$(PHP_EXEC) php artisan optimize
	$(PHP_EXEC) php artisan config:cache
	$(PHP_EXEC) php artisan route:cache
	$(PHP_EXEC) php artisan view:cache

##@ Assets

assets-dev: ## Compilar assets desarrollo
	cd Laravel_app && npm install && npm run dev &
	cd Vue_app && npm install && npm run dev &

assets-build: ## Compilar assets producción
	cd Laravel_app && npm install && npm run build
	cd Vue_app && npm install && npm run build

##@ Database

db-backup: ## Backup de base de datos
	docker compose exec db pg_dump -U postgres -d ${POSTGRES_DB} > backup_$(shell date +%Y%m%d_%H%M%S).sql
	@echo "✅ Backup creado"

db-restore: ## Restaurar backup (usar: make db-restore FILE=backup.sql)
	docker compose exec -T db psql -U postgres -d ${POSTGRES_DB} < $(FILE)
	@echo "✅ Base de datos restaurada"

db-connect: ## Conectar a PostgreSQL
	docker compose exec db psql -U postgres -d ${POSTGRES_DB}

##@ Docker

ps: ## Ver contenedores corriendo
	docker compose ps

logs: ## Ver logs
	docker compose logs -f

restart: ## Reiniciar servicios
	docker compose restart

shell-php: ## Shell en contenedor PHP
	docker compose exec php bash

shell-db: ## Shell en contenedor DB
	docker compose exec db bash

clean: ## Limpiar contenedores y volúmenes
	docker compose down -v
	docker system prune -f

health: ## Verificar salud de servicios
	@echo "Verificando servicios..."
	@docker compose ps
	@echo "\nVerificando PostgreSQL..."
	@docker compose exec -T db pg_isready -U postgres
	@echo "\nVerificando Redis..."
	@docker compose exec -T redis redis-cli ping

##@ Deploy

deploy-dev: ## Deploy a desarrollo
	git push origin develop

deploy-prod: ## Deploy a producción
	@echo "⚠️  Vas a desplegar a PRODUCCIÓN"
	@read -p "¿Estás seguro? [y/N]: " confirm && [ $$confirm = y ]
	git push origin main

initial-setup: ## Setup inicial del proyecto
	cp Laravel_app/.env.example Laravel_app/.env
	$(COMPOSE_DEV) --profile dev up -d
	sleep 15
	$(PHP_EXEC) composer install
	$(PHP_EXEC) php artisan key:generate
	$(PHP_EXEC) php artisan migrate:fresh --seed
	$(PHP_EXEC) php artisan shield:super-admin
	@echo "✅ Setup completado!"
