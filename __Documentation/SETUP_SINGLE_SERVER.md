# 🚀 Guía Completa: Dev y Prod en el MISMO Servidor con MISMO Proyecto

Esta guía explica cómo configurar ambientes de desarrollo y producción en un **único servidor VPS** usando el **mismo código fuente** pero con **contenedores Docker separados**.

**Servidor:** `66.97.43.244`  
**Estrategia:** Un solo repositorio, múltiples stacks de Docker

---

## 📋 Tabla de Contenidos

1. [Arquitectura del Setup](#arquitectura-del-setup)
2. [Preparación del Servidor](#preparación-del-servidor)
3. [Clonar y Configurar el Proyecto](#clonar-y-configurar-el-proyecto)
4. [Modificar Docker Compose](#modificar-docker-compose)
5. [Configurar Variables de Entorno](#configurar-variables-de-entorno)
6. [Levantar los Ambientes](#levantar-los-ambientes)
7. [Configurar GitHub Actions](#configurar-github-actions)
8. [Gestión y Mantenimiento](#gestión-y-mantenimiento)

---

## 🏗️ Arquitectura del Setup

### Estructura Física:

```
Servidor VPS: 66.97.43.244
├── /var/www/erp-filament/           # Un solo proyecto
│   ├── .env.production              # Variables producción
│   ├── .env.development             # Variables desarrollo
│   ├── docker-compose.base.yml      # Servicios base
│   ├── docker-compose.prod.yml      # Override producción
│   ├── docker-compose.dev.yml       # Override desarrollo
│   ├── Laravel_app/
│   └── Vue_app/
```

### Contenedores Running:

```
PRODUCCIÓN (Puerto 80, 443):
├── erp_prod_nginx
├── erp_prod_php
├── erp_prod_db (Puerto interno: 5432)
└── erp_prod_redis

DESARROLLO (Puerto 8080, 8081):
├── erp_dev_nginx
├── erp_dev_php
├── erp_dev_db (Puerto interno: 5433)
├── erp_dev_redis
├── erp_dev_node
└── erp_dev_mailpit
```

**Acceso:**
- Producción: `http://66.97.43.244:80` (público)
- Desarrollo: `http://66.97.43.244:8080` (solo para el equipo)

---

## 📍 PASO 1: Preparación del Servidor

### 1.1 Conectarse al Servidor

```bash
ssh root@66.97.43.244
```

### 1.2 Actualizar Sistema

```bash
apt update && apt upgrade -y
```

### 1.3 Instalar Docker y Docker Compose

```bash
# Instalar Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sh get-docker.sh

# Verificar instalación
docker --version
docker compose version
```

### 1.4 Crear Usuario Deploy

```bash
# Crear usuario
adduser deploy
# Ingresa contraseña cuando lo pida

# Agregar a grupos necesarios
usermod -aG docker deploy
usermod -aG sudo deploy

# Verificar
groups deploy
# Output: deploy : deploy docker sudo
```

### 1.5 Configurar SSH para el Usuario Deploy

```bash
# Cambiar al usuario deploy
su - deploy

# Crear directorio SSH
mkdir -p ~/.ssh
chmod 700 ~/.ssh

# Generar clave para GitHub Actions
ssh-keygen -t ed25519 -C "github-actions@66.97.43.244" -f ~/.ssh/github_actions
# Presiona Enter para passphrase vacía (importante para CI/CD)

# Agregar clave pública a authorized_keys
cat ~/.ssh/github_actions.pub >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys
chmod 600 ~/.ssh/github_actions

# Guardar la clave PRIVADA (para GitHub Secrets después)
cat ~/.ssh/github_actions
# Copia TODO el output (lo necesitarás después)
```

### 1.6 Configurar Git

```bash
# Como usuario deploy
git config --global user.name "Deploy Bot"
git config --global user.email "deploy@tudominio.com"
```

---

## 📍 PASO 2: Clonar y Configurar el Proyecto

### 2.1 Crear Directorio y Clonar

```bash
# Como usuario deploy
cd /var/www/

# Si no existe el directorio
sudo mkdir -p /var/www
sudo chown deploy:deploy /var/www

# Clonar el repositorio
git clone https://github.com/tu-usuario/erp-filament.git
cd erp-filament

# Verificar ramas
git branch -a
```

### 2.2 Crear Branch Develop (si no existe)

```bash
# Ver ramas
git branch -a

# Si no existe develop, créala
git checkout -b develop
git push origin develop
```

---

## 📍 PASO 3: Modificar Docker Compose para Ambos Ambientes

Necesitamos modificar los archivos Docker Compose para que usen puertos diferentes y nombres de contenedores distintos.

### 3.1 Editar `docker-compose.prod.yml`

```bash
nano docker-compose.prod.yml
```

**Modificar para usar puertos específicos:**

```yaml
# docker-compose.prod.yml
version: '3.8'

services:
  nginx:
    container_name: erp_prod_nginx
    ports:
      - "80:80"        # Producción en puerto 80
      - "443:443"      # HTTPS producción
    volumes:
      - ./docker/nginx/default.prod.conf:/etc/nginx/conf.d/default.conf
    networks:
      - erp_prod_network
    depends_on:
      - php

  php:
    container_name: erp_prod_php
    build:
      context: .
      dockerfile: Dockerfile
      target: production
    env_file:
      - .env.production    # Variables de producción
    networks:
      - erp_prod_network
    depends_on:
      - db
      - redis

  db:
    container_name: erp_prod_db
    image: postgres:16-alpine
    environment:
      POSTGRES_DB: ${DB_DATABASE:-erp_production}
      POSTGRES_USER: ${DB_USERNAME:-postgres}
      POSTGRES_PASSWORD: ${DB_PASSWORD}
    ports:
      - "127.0.0.1:5432:5432"  # Solo accesible desde localhost
    volumes:
      - postgres_prod_data:/var/lib/postgresql/data
    networks:
      - erp_prod_network

  redis:
    container_name: erp_prod_redis
    image: redis:7-alpine
    networks:
      - erp_prod_network
    volumes:
      - redis_prod_data:/data

networks:
  erp_prod_network:
    name: erp_prod_network
    driver: bridge

volumes:
  postgres_prod_data:
    name: erp_postgres_prod_data
  redis_prod_data:
    name: erp_redis_prod_data
```

### 3.2 Editar `docker-compose.dev.yml`

```bash
nano docker-compose.dev.yml
```

**Modificar para usar puertos diferentes:**

```yaml
# docker-compose.dev.yml
version: '3.8'

services:
  nginx:
    container_name: erp_dev_nginx
    ports:
      - "8080:80"      # Desarrollo en puerto 8080
    volumes:
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf
    networks:
      - erp_dev_network
    depends_on:
      - php

  php:
    container_name: erp_dev_php
    build:
      context: .
      dockerfile: Dockerfile
      target: development
    env_file:
      - .env.development   # Variables de desarrollo
    networks:
      - erp_dev_network
    depends_on:
      - db
      - redis

  db:
    container_name: erp_dev_db
    image: postgres:16-alpine
    environment:
      POSTGRES_DB: ${DB_DATABASE:-erp_development}
      POSTGRES_USER: ${DB_USERNAME:-postgres}
      POSTGRES_PASSWORD: ${DB_PASSWORD}
    ports:
      - "127.0.0.1:5433:5432"  # Puerto diferente a producción
    volumes:
      - postgres_dev_data:/var/lib/postgresql/data
    networks:
      - erp_dev_network

  redis:
    container_name: erp_dev_redis
    image: redis:7-alpine
    networks:
      - erp_dev_network
    volumes:
      - redis_dev_data:/data

  node:
    container_name: erp_dev_node
    profiles: ["dev"]
    image: node:20-alpine
    working_dir: /app
    ports:
      - "8081:5173"    # Vite dev server
    volumes:
      - ./Vue_app:/app
    command: sh -c "npm install && npm run dev -- --host"
    networks:
      - erp_dev_network

  mailpit:
    container_name: erp_dev_mailpit
    profiles: ["dev"]
    image: axllent/mailpit:latest
    ports:
      - "8025:8025"    # Web UI
      - "1025:1025"    # SMTP
    networks:
      - erp_dev_network

networks:
  erp_dev_network:
    name: erp_dev_network
    driver: bridge

volumes:
  postgres_dev_data:
    name: erp_postgres_dev_data
  redis_dev_data:
    name: erp_redis_dev_data
```

### 3.3 Verificar `docker-compose.base.yml`

```bash
cat docker-compose.base.yml
```

Este archivo debe tener **solo** las configuraciones base que son comunes, **sin** puertos ni nombres específicos.

---

## 📍 PASO 4: Configurar Variables de Entorno

### 4.1 Crear `.env.production`

```bash
nano .env.production
```

```bash
# .env.production
APP_NAME="ERP Filament"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://66.97.43.244

# Database
DB_CONNECTION=pgsql
DB_HOST=erp_prod_db
DB_PORT=5432
DB_DATABASE=erp_production
DB_USERNAME=postgres
DB_PASSWORD=tu_password_seguro_prod_123

# Cache
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Redis
REDIS_HOST=erp_prod_redis
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail (configurar con tu proveedor real)
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu-email@gmail.com
MAIL_PASSWORD=tu-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@tudominio.com
MAIL_FROM_NAME="${APP_NAME}"
```

### 4.2 Crear `.env.development`

```bash
nano .env.development
```

```bash
# .env.development
APP_NAME="ERP Filament DEV"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://66.97.43.244:8080

# Database
DB_CONNECTION=pgsql
DB_HOST=erp_dev_db
DB_PORT=5432
DB_DATABASE=erp_development
DB_USERNAME=postgres
DB_PASSWORD=dev_password_123

# Cache
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Redis
REDIS_HOST=erp_dev_redis
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail (usar Mailpit para desarrollo)
MAIL_MAILER=smtp
MAIL_HOST=erp_dev_mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=dev@localhost
MAIL_FROM_NAME="${APP_NAME}"

# Vite
VITE_DEV_SERVER_HOST=0.0.0.0
VITE_DEV_SERVER_PORT=5173
```

### 4.3 Copiar a Laravel_app

```bash
# Copiar ambos .env a Laravel_app/
cp .env.production Laravel_app/.env.production
cp .env.development Laravel_app/.env.development
```

---

## 📍 PASO 5: Levantar los Ambientes

### 5.1 Construir Imágenes

```bash
# Como usuario deploy en /var/www/erp-filament

# Construir imagen de producción
docker compose -f docker-compose.base.yml -f docker-compose.prod.yml build --no-cache

# Construir imagen de desarrollo
docker compose -f docker-compose.base.yml -f docker-compose.dev.yml build --no-cache
```

### 5.2 Iniciar PRODUCCIÓN

```bash
# Asegurarse de estar en branch main
git checkout main
git pull origin main

# Copiar .env de producción
cp .env.production Laravel_app/.env

# Iniciar contenedores de producción
docker compose -f docker-compose.base.yml -f docker-compose.prod.yml up -d

# Verificar contenedores
docker ps | grep prod

# Instalar dependencias de Laravel
docker compose -f docker-compose.base.yml -f docker-compose.prod.yml exec php composer install --no-dev --optimize-autoloader

# Generar key de Laravel
docker compose -f docker-compose.base.yml -f docker-compose.prod.yml exec php php artisan key:generate

# Ejecutar migraciones
docker compose -f docker-compose.base.yml -f docker-compose.prod.yml exec php php artisan migrate --force

# Cachear configuraciones
docker compose -f docker-compose.base.yml -f docker-compose.prod.yml exec php php artisan config:cache
docker compose -f docker-compose.base.yml -f docker-compose.prod.yml exec php php artisan route:cache
docker compose -f docker-compose.base.yml -f docker-compose.prod.yml exec php php artisan view:cache
```

### 5.3 Iniciar DESARROLLO

```bash
# En la MISMA carpeta, cambiar a branch develop
git checkout develop
git pull origin develop

# Copiar .env de desarrollo
cp .env.development Laravel_app/.env

# Iniciar contenedores de desarrollo (con profile dev)
docker compose -f docker-compose.base.yml -f docker-compose.dev.yml --profile dev up -d

# Verificar contenedores
docker ps | grep dev

# Instalar dependencias de Laravel (con dev)
docker compose -f docker-compose.base.yml -f docker-compose.dev.yml exec php composer install

# Generar key de Laravel
docker compose -f docker-compose.base.yml -f docker-compose.dev.yml exec php php artisan key:generate

# Ejecutar migraciones
docker compose -f docker-compose.base.yml -f docker-compose.dev.yml exec php php artisan migrate --seed

# NO cachear en desarrollo (para hot reload)
docker compose -f docker-compose.base.yml -f docker-compose.dev.yml exec php php artisan config:clear
```

### 5.4 Verificar Todo Está Corriendo

```bash
# Ver todos los contenedores
docker ps

# Deberías ver algo como:
# erp_prod_nginx    - Puerto 80, 443
# erp_prod_php
# erp_prod_db       - Puerto 5432 (interno)
# erp_prod_redis
# erp_dev_nginx     - Puerto 8080
# erp_dev_php
# erp_dev_db        - Puerto 5433 (interno)
# erp_dev_redis
# erp_dev_node      - Puerto 8081
# erp_dev_mailpit   - Puerto 8025

# Ver logs de producción
docker compose -f docker-compose.base.yml -f docker-compose.prod.yml logs -f nginx

# Ver logs de desarrollo
docker compose -f docker-compose.base.yml -f docker-compose.dev.yml logs -f nginx
```

### 5.5 Probar Acceso

```bash
# Producción
curl http://66.97.43.244:80
# Debería devolver página HTML

# Desarrollo
curl http://66.97.43.244:8080
# Debería devolver página HTML

# Mailpit (solo desarrollo)
curl http://66.97.43.244:8025
# Debería devolver interfaz de Mailpit
```

---

## 📍 PASO 6: Configurar GitHub Actions

### 6.1 Obtener Información para Secrets

```bash
# Como usuario deploy en el servidor

# 1. Usuario
whoami
# Output: deploy

# 2. Path del proyecto
pwd
# Output: /var/www/erp-filament

# 3. IP del servidor
curl ifconfig.me
# Output: 66.97.43.244

# 4. Clave SSH PRIVADA (copiar TODO)
cat ~/.ssh/github_actions
# Copiar desde -----BEGIN hasta -----END incluyendo esas líneas
```

### 6.2 Configurar Secrets en GitHub

Ve a: `https://github.com/tu-usuario/tu-repo/settings/secrets/actions`

Click en **New repository secret** y agrega:

#### Secrets para DESARROLLO:

```
Name: DEV_SSH_HOST
Value: 66.97.43.244
```

```
Name: DEV_SSH_USER
Value: deploy
```

```
Name: DEV_SSH_KEY
Value: -----BEGIN OPENSSH PRIVATE KEY-----
[Pega TODA la clave privada]
-----END OPENSSH PRIVATE KEY-----
```

```
Name: DEV_SSH_PORT
Value: 22
```

```
Name: DEV_APP_PATH
Value: /var/www/erp-filament
```

#### Secrets para PRODUCCIÓN:

```
Name: PROD_SSH_HOST
Value: 66.97.43.244
```

```
Name: PROD_SSH_USER
Value: deploy
```

```
Name: PROD_SSH_KEY
Value: -----BEGIN OPENSSH PRIVATE KEY-----
[Pega la MISMA clave privada que dev]
-----END OPENSSH PRIVATE KEY-----
```

```
Name: PROD_SSH_PORT
Value: 22
```

```
Name: PROD_APP_PATH
Value: /var/www/erp-filament
```

```
Name: PROD_DOMAIN
Value: 66.97.43.244
```

**Nota:** Como es el mismo servidor, la IP y la clave SSH son iguales. Solo cambia el proceso de deployment.

### 6.3 Modificar Workflows de GitHub Actions

#### Archivo: `.github/workflows/develop.yml`

Asegúrate que el deployment use los comandos correctos con las rutas completas:

```yaml
# Sección de deploy en develop.yml
- name: Deploy via SSH
  uses: appleboy/ssh-action@v1.1.0
  with:
    host: ${{ secrets.DEV_SSH_HOST }}
    username: ${{ secrets.DEV_SSH_USER }}
    key: ${{ secrets.DEV_SSH_KEY }}
    port: ${{ secrets.DEV_SSH_PORT || 22 }}
    script: |
      cd ${{ secrets.DEV_APP_PATH }}
      
      # Cambiar a branch develop
      git fetch origin
      git checkout develop
      git reset --hard origin/develop
      
      # Copiar .env de desarrollo
      cp .env.development Laravel_app/.env
      
      # Detener servicios de desarrollo
      docker compose -f docker-compose.base.yml -f docker-compose.dev.yml down
      
      # Rebuild si es necesario
      docker compose -f docker-compose.base.yml -f docker-compose.dev.yml build php
      
      # Iniciar servicios de desarrollo
      docker compose -f docker-compose.base.yml -f docker-compose.dev.yml --profile dev up -d
      
      # Esperar a que estén listos
      sleep 15
      
      # Instalar dependencias
      docker compose -f docker-compose.base.yml -f docker-compose.dev.yml exec -T php composer install --no-interaction
      
      # Migraciones
      docker compose -f docker-compose.base.yml -f docker-compose.dev.yml exec -T php php artisan migrate --force
      
      # Limpiar cache
      docker compose -f docker-compose.base.yml -f docker-compose.dev.yml exec -T php php artisan config:clear
      docker compose -f docker-compose.base.yml -f docker-compose.dev.yml exec -T php php artisan cache:clear
      
      echo "✅ Desarrollo desplegado en puerto 8080"
```

#### Archivo: `.github/workflows/production.yml`

```yaml
# Sección de deploy en production.yml
- name: Deploy via SSH
  uses: appleboy/ssh-action@v1.1.0
  with:
    host: ${{ secrets.PROD_SSH_HOST }}
    username: ${{ secrets.PROD_SSH_USER }}
    key: ${{ secrets.PROD_SSH_KEY }}
    port: ${{ secrets.PROD_SSH_PORT || 22 }}
    command_timeout: 20m
    script: |
      set -e
      
      cd ${{ secrets.PROD_APP_PATH }}
      
      # Cambiar a branch main
      git fetch origin
      git checkout main
      git reset --hard origin/main
      
      # Copiar .env de producción
      cp .env.production Laravel_app/.env
      
      echo "🛑 Mantenimiento..."
      docker compose -f docker-compose.base.yml -f docker-compose.prod.yml exec -T php php artisan down --retry=60 || true
      
      echo "🐳 Deteniendo servicios de producción..."
      docker compose -f docker-compose.base.yml -f docker-compose.prod.yml down
      
      echo "🔨 Reconstruyendo PHP..."
      docker compose -f docker-compose.base.yml -f docker-compose.prod.yml build --no-cache php
      
      echo "🚀 Iniciando servicios de producción..."
      docker compose -f docker-compose.base.yml -f docker-compose.prod.yml up -d
      
      echo "⏳ Esperando servicios..."
      sleep 30
      
      echo "📦 Instalando dependencias..."
      docker compose -f docker-compose.base.yml -f docker-compose.prod.yml exec -T php composer install --no-dev --optimize-autoloader --no-interaction
      
      echo "🔄 Migraciones..."
      docker compose -f docker-compose.base.yml -f docker-compose.prod.yml exec -T php php artisan migrate --force --no-interaction
      
      echo "🧹 Optimizando..."
      docker compose -f docker-compose.base.yml -f docker-compose.prod.yml exec -T php php artisan config:cache
      docker compose -f docker-compose.base.yml -f docker-compose.prod.yml exec -T php php artisan route:cache
      docker compose -f docker-compose.base.yml -f docker-compose.prod.yml exec -T php php artisan view:cache
      
      echo "✅ Producción online..."
      docker compose -f docker-compose.base.yml -f docker-compose.prod.yml exec -T php php artisan up
      
      echo "🎉 Producción desplegada en puerto 80"
```

---

## 📍 PASO 7: Gestión y Mantenimiento

### 7.1 Comandos Útiles

#### Gestión de Producción:

```bash
cd /var/www/erp-filament

# Ver logs
docker compose -f docker-compose.base.yml -f docker-compose.prod.yml logs -f

# Reiniciar servicio específico
docker compose -f docker-compose.base.yml -f docker-compose.prod.yml restart php

# Entrar al contenedor
docker compose -f docker-compose.base.yml -f docker-compose.prod.yml exec php bash

# Detener todo
docker compose -f docker-compose.base.yml -f docker-compose.prod.yml down

# Iniciar todo
docker compose -f docker-compose.base.yml -f docker-compose.prod.yml up -d
```

#### Gestión de Desarrollo:

```bash
cd /var/www/erp-filament

# Ver logs
docker compose -f docker-compose.base.yml -f docker-compose.dev.yml logs -f

# Reiniciar servicio específico
docker compose -f docker-compose.base.yml -f docker-compose.dev.yml restart php

# Entrar al contenedor
docker compose -f docker-compose.base.yml -f docker-compose.dev.yml exec php bash

# Detener todo
docker compose -f docker-compose.base.yml -f docker-compose.dev.yml down

# Iniciar todo
docker compose -f docker-compose.base.yml -f docker-compose.dev.yml --profile dev up -d
```

### 7.2 Cambiar Entre Branches

```bash
# Para trabajar en desarrollo
git checkout develop
cp .env.development Laravel_app/.env

# Para trabajar en producción
git checkout main
cp .env.production Laravel_app/.env
```

### 7.3 Crear Aliases para Facilitar el Trabajo

```bash
# Agregar al ~/.bashrc o ~/.zshrc del usuario deploy
nano ~/.bashrc
```

```bash
# Aliases para Docker Compose
alias dc-prod="docker compose -f docker-compose.base.yml -f docker-compose.prod.yml"
alias dc-dev="docker compose -f docker-compose.base.yml -f docker-compose.dev.yml"

# Ejemplos de uso después:
# dc-prod up -d
# dc-prod logs -f
# dc-dev exec php bash
```

```bash
# Recargar configuración
source ~/.bashrc
```

### 7.4 Monitorear Recursos

```bash
# Ver uso de CPU y RAM por contenedor
docker stats

# Ver espacio en disco
df -h

# Ver logs del sistema
journalctl -u docker -f

# Ver todos los contenedores (running y stopped)
docker ps -a
```

### 7.5 Backup de Bases de Datos

```bash
# Backup de producción
docker compose -f docker-compose.base.yml -f docker-compose.prod.yml exec -T db \
  pg_dump -U postgres erp_production > backup_prod_$(date +%Y%m%d_%H%M%S).sql

# Backup de desarrollo
docker compose -f docker-compose.base.yml -f docker-compose.dev.yml exec -T db \
  pg_dump -U postgres erp_development > backup_dev_$(date +%Y%m%d_%H%M%S).sql
```

### 7.6 Restaurar Base de Datos

```bash
# Restaurar en producción
cat backup_prod_20260222_123456.sql | \
  docker compose -f docker-compose.base.yml -f docker-compose.prod.yml exec -T db \
  psql -U postgres -d erp_production

# Restaurar en desarrollo
cat backup_dev_20260222_123456.sql | \
  docker compose -f docker-compose.base.yml -f docker-compose.dev.yml exec -T db \
  psql -U postgres -d erp_development
```

---

## 🔥 Troubleshooting

### Problema: Los contenedores no inician

```bash
# Ver logs detallados
docker compose -f docker-compose.base.yml -f docker-compose.prod.yml logs

# Verificar puertos en uso
sudo netstat -tulpn | grep LISTEN

# Verificar conflictos de redes
docker network ls
```

### Problema: Puerto ya en uso

```bash
# Ver qué está usando el puerto 80
sudo lsof -i :80

# Ver qué está usando el puerto 8080
sudo lsof -i :8080

# Matar proceso si es necesario
sudo kill -9 <PID>
```

### Problema: Git dice "please commit or stash"

```bash
# Ver qué cambió
git status

# Descartar todos los cambios locales
git reset --hard

# Limpiar archivos no rastreados
git clean -fd
```

### Problema: Database connection error

```bash
# Verificar que el contenedor de DB está corriendo
docker ps | grep db

# Ver logs de la base de datos
docker compose -f docker-compose.base.yml -f docker-compose.prod.yml logs db

# Verificar .env tiene el nombre correcto del host
cat Laravel_app/.env | grep DB_HOST
# Debe ser: erp_prod_db (para prod) o erp_dev_db (para dev)
```

### Problema: No hay espacio en disco

```bash
# Ver espacio disponible
df -h

# Limpiar contenedores detenidos
docker container prune -f

# Limpiar imágenes no usadas
docker image prune -a -f

# Limpiar volúmenes no usados (CUIDADO: puede borrar datos)
docker volume prune -f

# Limpiar todo (CUIDADO)
docker system prune -a -f --volumes
```

---

## ✅ Checklist de Verificación

### Servidor:
- [ ] Docker y Docker Compose instalados
- [ ] Usuario `deploy` creado
- [ ] Usuario `deploy` en grupo `docker`
- [ ] Claves SSH configuradas
- [ ] Proyecto clonado en `/var/www/erp-filament`

### Archivos de Configuración:
- [ ] `.env.production` creado y configurado
- [ ] `.env.development` creado y configurado
- [ ] `docker-compose.prod.yml` con puertos correctos
- [ ] `docker-compose.dev.yml` con puertos correctos
- [ ] Puertos no están en conflicto

### Contenedores:
- [ ] Contenedores de producción corriendo (puerto 80)
- [ ] Contenedores de desarrollo corriendo (puerto 8080)
- [ ] Bases de datos separadas (5432 y 5433)
- [ ] Ambos ambientes accesibles

### GitHub:
- [ ] Secrets configurados en GitHub
- [ ] Workflows modificados correctamente
- [ ] Push a `develop` dispara deployment a dev
- [ ] Push a `main` dispara deployment a prod

### Acceso:
- [ ] `http://66.97.43.244:80` muestra producción
- [ ] `http://66.97.43.244:8080` muestra desarrollo
- [ ] `http://66.97.43.244:8025` muestra Mailpit

---

## 🎯 Flujo de Trabajo Diario

### Developer haciendo cambios:

```bash
# 1. Crear rama de feature local
git checkout develop
git pull origin develop
git checkout -b feature/nueva-funcionalidad

# 2. Hacer cambios y commits
git add .
git commit -m "feat: nueva funcionalidad"

# 3. Push a GitHub
git push origin feature/nueva-funcionalidad

# 4. Crear Pull Request a develop en GitHub

# 5. Merge del PR → Auto-deploy a desarrollo (puerto 8080)

# 6. Probar en: http://66.97.43.244:8080

# 7. Si todo OK, crear PR de develop → main

# 8. Merge a main → Auto-deploy a producción (puerto 80)
```

---

## 📊 Ventajas de Este Setup

✅ **Un solo servidor:** Reduces costos  
✅ **Un solo repositorio:** Fácil de mantener  
✅ **Ambientes aislados:** Bases de datos separadas  
✅ **Puertos diferentes:** No hay conflictos  
✅ **CI/CD automático:** Push y se despliega solo  
✅ **Fácil rollback:** Git reset + rebuild  

---

## ⚠️ Limitaciones

⚠️ **Recursos compartidos:** CPU y RAM se comparten  
⚠️ **Un fallo afecta ambos:** Si el servidor cae, ambos caen  
⚠️ **Mismo código:** Cambios en develop pueden afectar prod si no se maneja bien  

---

## 🚀 Mejoras Futuras

Cuando tu proyecto crezca, considera:

1. **Comprar un dominio:**
   - `erp.tudominio.com` → Producción
   - `dev.erp.tudominio.com` → Desarrollo

2. **Separar servidores:**
   - Servidor 1: Solo producción
   - Servidor 2: Desarrollo y staging

3. **Agregar SSL:**
   ```bash
   apt install certbot
   certbot --nginx -d erp.tudominio.com
   ```

4. **Monitoreo:**
   - Instalar Portainer para gestión visual de Docker
   - Configurar alertas con UptimeRobot

---

¿Todo claro? ¡Ahora tienes dev y prod en el mismo servidor! 🎉
