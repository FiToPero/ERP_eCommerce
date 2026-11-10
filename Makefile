# Makefile
# Comandos útiles para desarrollo y producción
# ssh -p5064 fito@66.87.43.244  #############
.PHONY: help dev prod start stop restart logs build clean test migrate seed fresh deploy health

# Variables
COMPOSE_DEV = docker compose -f docker-compose.dev.yml
COMPOSE_PROD = docker compose -f docker-compose.prod.yml
PHP_EXEC = $(COMPOSE_DEV) exec -w /var/www/html/Laravel_app php

help: ## Mostrar esta ayuda
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

##@ Desarrollo

dev: ## Iniciar ambiente de desarrollo
	$(COMPOSE_DEV) --profile dev up -d
	@echo "✅ Ambiente de desarrollo iniciado"
	@echo "Laravel: http://localhost:8090"
	@echo "Vue.js: http://localhost:8081"
	@echo "Mailpit: http://localhost:8025"
	@echo "configurar archivo /etc/hosts:  127.0.0.1 cap-erp-admin-dev.com / cap-erp-ecommerce-dev.com" 

dev-build: ## Construir e iniciar desarrollo
	$(COMPOSE_DEV) --profile dev build --no-cache
	$(COMPOSE_DEV) --profile dev up -d

dev-stop: ## Detener desarrollo
	$(COMPOSE_DEV) --profile dev down
	@echo "✅ Ambiente de desarrollo detenido"

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
	@echo "✅ Ambiente de producción detenido"

prod-logs: ## Ver logs de producción
	$(COMPOSE_PROD) logs -f

##@ Laravel

install: ## Instalar dependencias PHP
	$(PHP_EXEC) composer install

make: ## ejecuto comando make de Laravel
	$(PHP_EXEC) php artisan make:$(CMD) $(NAME) $(ARGS)

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
	cd Vue_ecommerce_app && npm install && npm run dev &

assets-build: ## Compilar assets producción
	cd Laravel_app && npm install && npm run build
	cd Vue_ecommerce_app && npm install && npm run build

##@ Database

db-backup: ## Backup de base de datos
	$(COMPOSE_DEV) exec db pg_dump -U postgres -d ${POSTGRES_DB} > backup_$(shell date +%Y%m%d_%H%M%S).sql
	@echo "✅ Backup creado"

db-restore: ## Restaurar backup (usar: make db-restore FILE=backup.sql)
	$(COMPOSE_DEV) exec -T db psql -U postgres -d ${POSTGRES_DB} < $(FILE)
	@echo "✅ Base de datos restaurada"

db-connect: ## Conectar a PostgreSQL
	$(COMPOSE_DEV) exec db psql -U postgres -d ${POSTGRES_DB}

##@ Docker

ps: ## Ver contenedores corriendo
	$(COMPOSE_DEV) --profile dev ps

logs: ## Ver logs
	$(COMPOSE_DEV) --profile dev logs -f

restart: ## Reiniciar servicios
	$(COMPOSE_DEV) --profile dev restart

shell-php: ## Shell en contenedor PHP
	$(COMPOSE_DEV) exec -it php bash

shell-db: ## Shell en contenedor DB
	$(COMPOSE_DEV) exec -it db bash

clean: ## Limpiar contenedores y volúmenes
	$(COMPOSE_DEV) down -v
	docker system prune -f

health: ## Verificar salud de servicios
	@echo "Verificando servicios..."
	@$(COMPOSE_DEV) --profile dev ps
	@echo "\nVerificando PostgreSQL..."
	@$(COMPOSE_DEV) exec -T db pg_isready -U postgres
	@echo "\nVerificando Redis..."
	@$(COMPOSE_DEV) exec -T redis redis-cli ping

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


### cron server 
##  file crontab -e
##  agregar la siguiente línea para ejecutar el cron cada minuto
##  */10 * * * * cd /opt/ERP_eCommerce && docker compose -f docker-compose.dev.yml exec -T php php artisan schedule:run >> /opt/ERP_eCommerce/storage/logs/scheduler.log 2>&1

# ┌───(fito@FiTo)-[~/AAdocker/ERP_filament][develop*] 
# └─$ docker compose -f docker-compose.dev.yml exec -w /var/www/html/Laravel_app php php artisan schedule:list 
#  */10 * * * *  php artisan sessions:close-stale ............................................................................................. Next Due: 7 minutes from now
#  
# o ejecutar directamente el comando: php artisan sessions:close-stale dentro del contenedor.

#############################################3
# Agregar en servidor cron crontab
# en el server abrir crontab con:
# crontab -e
# y agregar la línea:
# 
# * * * * * cd /ruta/al/proyecto && docker compose -f docker-compose.dev.yml exec -T -w /var/www/html/Laravel_app php php artisan schedule:run >> /dev/null 2>&1
#
# Guardar y comprueba que quedó registrado con crontab -l.
#############################################

# Para probar sin esperar al cron,
# ejecuta esa misma línea a mano (sin los cinco asteriscos)
# o php artisan sessions:close-stale dentro del contenedor.

cron: ## Ejecutar tareas programadas de Laravel
	$(PHP_EXEC) php artisan schedule:run


##########################  Para hacer el server VPS
#   SERVER
##   para hacer el el server VPS
#################################
# Install Fail2ban
# sudo apt install fail2ban
# sudo apt update && sudo apt upgrade -y ## automatico
# Tailscale -> VPN privada ## opcional
