# ERP Filament - Guía de Deployment

Sistema ERP basado en Laravel + Filament con frontend Vue.js, completamente dockerizado.

AO28
---

## 📑 Índice Rápido

- [🔧 Setup Servidor DESARROLLO (Primera Vez)](#-setup-servidor-desarrollo-primera-vez)
  - [Comandos Útiles para Desarrollo](#-comandos-útiles-para-desarrollo)
  - [💡 Workflow de Desarrollo (Recomendado)](#-workflow-de-desarrollo-recomendado)
- [🚀 Deployment en PRODUCCIÓN](#-deployment-en-producción)
- [🔄 Comandos Útiles Post-Instalación](#️-comandos-útiles-post-instalación) (Producción)
- [🔄 Actualizaciones Futuras](#-actualizaciones-futuras)
- [🐛 Troubleshooting](#-troubleshooting)
- [📚 Documentación Adicional](#-documentación-adicional)

---

## 🔧 Setup Servidor DESARROLLO (Primera Vez)

### Requisitos Previos
- Servidor Ubuntu 20.04+
- Acceso SSH con usuario sudo
- Dominio/IP del servidor

### Paso 1: Conectar al Servidor

```bash
# Conectar via SSH
ssh usuario@dev.ejemplo.com
# O con IP: ssh usuario@192.168.1.100
```

### Paso 2: Instalar Docker

```bash
# Actualizar sistema
sudo apt update && sudo apt upgrade -y

# Instalar dependencias
sudo apt install -y apt-transport-https ca-certificates curl software-properties-common git

# Instalar Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Agregar usuario al grupo docker (evita usar sudo)
sudo usermod -aG docker $USER

# Cerrar y reconectar SSH para aplicar cambios
exit
ssh usuario@dev.ejemplo.com

# Verificar instalación
docker --version
docker compose version
```

### Paso 3: Clonar Repositorio

```bash
# Ir a directorio de trabajo
cd /var/www
cd   /opt

# Clonar proyecto (con SSH - recomendado)
sudo git clone git@github.com:tuempresa/erp-filament.git

# O con HTTPS si no tienes SSH configurado
sudo git clone https://github.com/tuempresa/erp-filament.git

# Cambiar permisos
sudo chown -R $USER:$USER erp-filament
cd erp-filament

# Cambiar a rama develop
git checkout develop
git pull origin develop
```

**Si usas SSH y no tienes clave configurada:**

hacer los pasos del archivo server_ssh.md

### Paso 4: Configurar .env

```bash
# Crear .env principal (root del proyecto)
cp .env.example .env
nano .env

# Contenido básico:
APP_ENV=development
APP_DEBUG=true
```

```bash
# Crear .env de Laravel
cd Laravel_app
cp .env.example .env
nano .env
```

**Configuración de Laravel .env:**
```env
APP_NAME="ERP Development"
APP_ENV=development
APP_DEBUG=true
APP_URL=http://dev.ejemplo.com

DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=erp_development
DB_USERNAME=postgres
DB_PASSWORD=dev_password_123

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

VITE_APP_NAME="${APP_NAME}"
```

**Volver al root:**
```bash
cd ..
```

### Paso 5: Iniciar Servicios Docker

```bash
# Construir e iniciar contenedores (incluye node + vue para HMR)
docker compose -f docker-compose.base.yml -f docker-compose.dev.yml --profile dev up -d --build

# Ver logs en tiempo real (Ctrl+C para salir, contenedores siguen corriendo)
docker compose logs -f

# Esperar hasta ver:
# php       | NOTICE: ready to handle connections
# db        | database system is ready to accept connections
# node      | VITE v5.x.x ready in xxx ms
# vue       | VITE v5.x.x ready in xxx ms
```

**Tiempo estimado:** 2-5 minutos

**Servicios iniciados:**
- **nginx:** http://localhost:8090 (aplicación)
- **php-fpm:** Procesa código PHP
- **postgresql:** Base de datos
- **redis:** Cache y sesiones
- **node:** Vite dev server para Laravel (http://localhost:5173) - HMR automático
- **vue:** Vite dev server para Vue.js (http://localhost:5174) - HMR automático
- **mailpit:** Captura emails (http://localhost:8025)

**⚡ Hot Module Replacement activo:** Los cambios en código frontend se reflejan automáticamente sin recargar el navegador.

**Tiempo estimado:** 2-5 minutos

### Paso 6: Instalar Dependencias

```bash
# Instalar dependencias de PHP (Composer)
docker compose exec -w /var/www/html/Laravel_app php composer install

# Generar clave de aplicación
docker compose exec -w /var/www/html/Laravel_app php php artisan key:generate

# Verificar que APP_KEY se generó
docker compose exec -w /var/www/html/Laravel_app php grep APP_KEY .env
```

### Paso 7: Configurar Base de Datos

```bash
# Ejecutar migraciones
docker compose exec -w /var/www/html/Laravel_app php php artisan migrate

# Generar permisos de Filament Shield
docker compose exec -w /var/www/html/Laravel_app php php artisan shield:generate --all

# Crear super admin
docker compose exec -w /var/www/html/Laravel_app php php artisan shield:super-admin
```

**Se te pedirá:**
- Nombre: `Admin Dev`
- Email: `admin@dev.com`
- Contraseña: `(tu contraseña segura)`

**⚠️ Guardar credenciales.**

### Paso 8: Verificar Instalación

```bash
# Ver estado de contenedores
docker compose ps
# Todos deben estar "Up"

# Probar conexión
curl http://localhost
# Debe responder con HTML

# Ver logs por si hay errores
docker compose logs --tail=50
```

### Paso 9: Acceder al Sistema

**Aplicación principal:**
- **Con dominio:** http://dev.ejemplo.com:8090/admin
- **Localhost:** http://localhost:8090/admin
- **Con IP:** http://192.168.1.100:8090/admin

**Servicios de desarrollo disponibles:**

| Servicio | URL | Descripción |
|----------|-----|-------------|
| **Aplicación** | http://localhost:8090 | ERP Filament (nginx) |
| **Vite Laravel** | http://localhost:5173 | Dev server de Vite (Laravel assets) |
| **Vite Vue** | http://localhost:5174 | Dev server de Vite (Vue.js) |
| **Mailpit** | http://localhost:8025 | Captura de emails de desarrollo |
| **PostgreSQL** | localhost:5432 | Base de datos (usar pgAdmin/DBeaver) |
| **Redis** | localhost:6379 | Cache (usar Redis Commander) |

**Credenciales de acceso:** Las que creaste en el Paso 7.

Ingresa con las credenciales del Paso 7.

**✅ ¡Servidor de Desarrollo Listo!**

---

### 🔧 Comandos Útiles para Desarrollo

#### Gestión de Contenedores

```bash
# Ver estado
docker compose ps

# Ver logs de todos los servicios
docker compose logs -f

# Ver logs de un servicio específico
docker compose logs -f php
docker compose logs -f nginx
docker compose logs -f db
docker compose logs -f node      # Vite Laravel - Ver HMR
docker compose logs -f vue       # Vite Vue - Ver HMR
docker compose logs -f mailpit   # Emails capturados

# Reiniciar servicios
docker compose restart
docker compose restart php

# Reiniciar frontend (si HMR deja de funcionar)
docker compose restart node vue

# Detener todo
docker compose down

# Iniciar de nuevo
docker compose -f docker-compose.base.yml -f docker-compose.dev.yml --profile dev up -d

# Reconstruir contenedores (después de cambios en Dockerfile)
docker compose -f docker-compose.base.yml -f docker-compose.dev.yml --profile dev up -d --build
```

#### Comandos Artisan

```bash
# Template general
docker compose exec -w /var/www/html/Laravel_app php php artisan <comando>

# Ejemplos comunes:
docker compose exec -w /var/www/html/Laravel_app php php artisan migrate
docker compose exec -w /var/www/html/Laravel_app php php artisan migrate:fresh --seed
docker compose exec -w /var/www/html/Laravel_app php php artisan db:seed
docker compose exec -w /var/www/html/Laravel_app php php artisan tinker
docker compose exec -w /var/www/html/Laravel_app php php artisan route:list
docker compose exec -w /var/www/html/Laravel_app php php artisan cache:clear
docker compose exec -w /var/www/html/Laravel_app php php artisan config:clear
docker compose exec -w /var/www/html/Laravel_app php php artisan queue:work
```

#### Git Operations

```bash
# Actualizar código
git pull origin develop

# Ver cambios
git status
git log --oneline -10

# Cambiar de rama
git checkout main
git checkout develop
git checkout -b feature/nueva-feature
```

#### Composer

```bash
# Instalar/actualizar dependencias
docker compose exec -w /var/www/html/Laravel_app php composer install
docker compose exec -w /var/www/html/Laravel_app php composer update

# Agregar paquete
docker compose exec -w /var/www/html/Laravel_app php composer require vendor/package
```

#### NPM y Assets de Frontend

**⚠️ IMPORTANTE: NO ejecutar `npm run dev` manualmente**

Los contenedores `node` y `vue` ya ejecutan automáticamente `npm run dev` cuando levantas el entorno con `--profile dev`. Esto proporciona **Hot Module Replacement (HMR)** automático.

**URLs de desarrollo:**
- **Laravel (Vite):** http://localhost:5173
- **Vue.js:** http://localhost:5174
- **Aplicación:** http://localhost:8090

**Los assets se recargan automáticamente** al guardar cambios en:
- `Laravel_app/resources/js/**`
- `Laravel_app/resources/css/**`
- `Vue_app/src/**`

**Si necesitas instalar/actualizar dependencias:**

```bash
# Laravel - Instalar dependencias (dentro del contenedor node)
docker compose exec node sh -c "cd Laravel_app && npm install"

# Vue - Instalar dependencias (dentro del contenedor vue)
docker compose exec vue npm install

# O manualmente en tu máquina local (también válido):
cd Laravel_app && npm install && cd ..
cd Vue_app && npm install && cd ..
```

**Para compilar assets en producción (solo antes de deploy):**

```bash
# Compilar en tu máquina local, NO en servidor
cd Laravel_app
npm install
npm run build  # Genera Laravel_app/public/build/

cd ../Vue_app
npm install
npm run build  # Genera Vue_app/dist/
```

**Ver logs de los contenedores de frontend:**

```bash
# Ver logs de Vite (Laravel)
docker compose logs -f node

# Ver logs de Vue dev server
docker compose logs -f vue
```

#### Base de Datos

```bash
# Conectar a PostgreSQL
docker compose exec db psql -U postgres -d erp_development

# Comandos útiles en psql:
\dt            # Listar tablas
\d tabla       # Describir tabla
\q             # Salir

# Backup
docker compose exec -T db pg_dump -U postgres -d erp_development > backup_dev_$(date +%Y%m%d).sql

# Restore
cat backup_dev_20260221.sql | docker compose exec -T db psql -U postgres -d erp_development

# Ver tamaño de base de datos
docker compose exec db psql -U postgres -c "SELECT pg_size_pretty(pg_database_size('erp_development'));"
```

#### Monitoreo

```bash
# Uso de recursos
docker stats --no-stream

# Espacio en disco
df -h
docker system df

# Ver procesos PHP
docker compose exec php ps aux

# Ver conexiones de base de datos
docker compose exec db psql -U postgres -c "SELECT count(*) FROM pg_stat_activity;"
```

#### Limpiar Sistema

```bash
# Limpiar imágenes y contenedores no usados
docker system prune -a

# Limpiar volúmenes no usados
docker volume prune

# Limpiar todo (cuidado: borra datos de DB si no hay volúmenes nombrados)
docker compose down -v
```

---

### 💡 Workflow de Desarrollo (Recomendado)

**Cómo trabajar con Hot Module Replacement (HMR):**

1. **Levantar servicios una sola vez:**
   ```bash
   docker compose -f docker-compose.base.yml -f docker-compose.dev.yml --profile dev up -d
   ```

2. **Abrir navegador en:**
   - http://localhost:8090/admin (aplicación)
   - Mantener DevTools abierto (F12)

3. **Editar código:**
   - **Frontend Laravel:** Modifica `Laravel_app/resources/js/**` o `Laravel_app/resources/css/**`
   - **Frontend Vue:** Modifica `Vue_app/src/**`
   - **Backend PHP:** Modifica `Laravel_app/app/**` o `Laravel_app/routes/**`

4. **Ver cambios automáticamente:**
   - **Frontend:** Se recarga instantáneamente sin F5 (HMR)
   - **Backend:** Recarga manual (F5) o usa Laravel Pint/Debugbar

5. **Si HMR deja de funcionar:**
   ```bash
   # Reiniciar contenedores de frontend
   docker compose restart node vue
   
   # Ver logs para diagnosticar
   docker compose logs -f node vue
   ```

**Ventajas de este setup:**
- ✅ Cambios en CSS/JS visibles en <1 segundo
- ✅ Estado de Vue/React preservado en recarga
- ✅ No necesitas ejecutar `npm run dev` manualmente
- ✅ Múltiples desarrolladores pueden trabajar sin conflictos de puertos
- ✅ Consistente entre diferentes máquinas

**Archivos que NO disparan HMR (requieren reinicio manual):**
- `.env` → `docker compose restart php`
- `docker-compose*.yml` → `docker compose down && docker compose up -d`
- `composer.json` → `docker compose exec php composer install`
- `package.json` → `docker compose restart node vue`

---

## 🚀 Deployment en PRODUCCIÓN

### Contexto

Si ya tienes el proyecto clonado en producción, estás en la rama `main` y has configurado los `.env`, **sigue estos pasos**:

---

## 🔨 Pasos para Primer Deployment en Producción

### Paso 1: Compilar Assets (Opción Optimizada)

Como preguntas sobre `npm install`, aquí está la forma más ligera:

```bash
# Compilar Laravel assets (Vite + Tailwind)
cd Laravel_app
npm ci --omit=optional --no-audit --no-fund
npm run build
rm -rf node_modules  # Limpiar después de compilar
cd ..

# Compilar Vue.js assets
cd Vue_app
npm ci --omit=optional --no-audit --no-fund
npm run build
rm -rf node_modules  # Limpiar después de compilar
cd ..
```

**¿Por qué estos flags?**
- `npm ci`: Instalación limpia desde `package-lock.json` (más rápido y determinístico)
- `--omit=optional`: Salta dependencias opcionales (ahorra espacio)
- `--no-audit --no-fund`: Salta checks de seguridad y mensajes de funding (más rápido)
- `rm -rf node_modules`: Los assets compilados están en `public/build/`, no necesitas node_modules en producción

**Resultado esperado:**
- `Laravel_app/public/build/` → Assets compilados de Laravel
- `Vue_app/dist/` → Assets compilados de Vue

---

### Paso 2: Configurar SSL (Si usas HTTPS)

#### Opción A: Con Let's Encrypt (Recomendado - Gratis)

```bash
# Instalar certbot si no lo tienes
sudo apt update && sudo apt install certbot -y

# Obtener certificado (cambia tu-dominio.com)
sudo certbot certonly --standalone -d erp.tuempresa.com

# Crear directorio para certificados
mkdir -p docker/nginx/ssl

# Copiar certificados al proyecto
sudo cp /etc/letsencrypt/live/erp.tuempresa.com/fullchain.pem docker/nginx/ssl/certificate.crt
sudo cp /etc/letsencrypt/live/erp.tuempresa.com/privkey.pem docker/nginx/ssl/private.key

# Dar permisos correctos
sudo chown $(whoami):$(whoami) docker/nginx/ssl/*
chmod 600 docker/nginx/ssl/*
```

#### Opción B: Con Certificados Comprados

```bash
# Crear directorio
mkdir -p docker/nginx/ssl

# Copiar tus certificados
cp /ruta/a/tu/certificado.crt docker/nginx/ssl/certificate.crt
cp /ruta/a/tu/llave.key docker/nginx/ssl/private.key

chmod 600 docker/nginx/ssl/*
```

#### Opción C: Sin SSL (Solo HTTP - No recomendado en producción)

Si NO vas a usar SSL, edita `docker-compose.prod.yml` y comenta las líneas del certificado:

```yaml
nginx:
  # Comentar estas líneas en docker-compose.prod.yml
  # volumes:
  #   - ./docker/nginx/ssl:/etc/nginx/ssl:ro
```

---

### Paso 3: Iniciar Contenedores Docker

```bash
# Construir e iniciar servicios de producción
docker compose -f docker-compose.base.yml -f docker-compose.prod.yml up -d --build

# Ver el progreso
docker compose logs -f
```

**Espera ~30-60 segundos** hasta que veas:
```
php       | NOTICE: ready to handle connections
nginx     | nginx: [notice] start worker processes
db        | database system is ready to accept connections
```

Presiona `Ctrl+C` para salir de los logs (los contenedores siguen corriendo).

---

### Paso 4: Instalar Dependencias de Laravel

```bash
# Instalar composer (sin dependencias de desarrollo)
docker compose exec -w /var/www/html/Laravel_app php composer install --no-dev --optimize-autoloader

# Generar clave de aplicación (si no la tienes en .env)
docker compose exec -w /var/www/html/Laravel_app php php artisan key:generate
```

**Tiempo estimado:** 2-3 minutos

---

### Paso 5: Configurar Base de Datos

```bash
# Ejecutar migraciones (creará todas las tablas)
docker compose exec -w /var/www/html/Laravel_app php php artisan migrate --force

# Generar permisos de Filament Shield
docker compose exec -w /var/www/html/Laravel_app php php artisan shield:generate --all

# Crear tu primer usuario administrador
docker compose exec -w /var/www/html/Laravel_app php php artisan shield:super-admin
```

**Se te pedirá:**
- Nombre del super admin
- Email
- Contraseña

**⚠️ Guarda estas credenciales en un lugar seguro.**

---

### Paso 6: Optimizar para Producción

```bash
# Cachear configuraciones (mejora rendimiento)
docker compose exec -w /var/www/html/Laravel_app php php artisan config:cache
docker compose exec -w /var/www/html/Laravel_app php php artisan route:cache
docker compose exec -w /var/www/html/Laravel_app php php artisan view:cache

# Generar autoload optimizado
docker compose exec -w /var/www/html/Laravel_app php composer dump-autoload --optimize --no-dev
```

---

### Paso 7: Verificar Instalación

```bash
# Ver estado de contenedores (todos deben estar "Up")
docker compose ps

# Verificar salud de la aplicación
curl http://localhost/health
# O si tienes dominio:
curl https://erp.tuempresa.com/health
```

**Respuesta esperada:** `healthy` o HTTP 200

---

### Paso 8: Acceder al Sistema

Abre tu navegador:

- **Con dominio:** https://erp.tuempresa.com/admin
- **Sin dominio (solo IP):** http://tu-ip-servidor/admin

Ingresa con las credenciales que creaste en el Paso 5.

---

## ✅ Checklist Post-Instalación

Verifica estos puntos críticos de seguridad:

```bash
# 1. Verificar que DEBUG está desactivado
docker compose exec -w /var/www/html/Laravel_app php grep APP_DEBUG .env
# Debe mostrar: APP_DEBUG=false

# 2. Verificar que PHP OPcache está activo
docker compose exec php php -i | grep opcache.enable
# Debe mostrar: opcache.enable => On => On

# 3. Verificar que servicios node/vue NO están corriendo
docker compose ps | grep node
docker compose ps | grep vue
# NO debe mostrar nada (estos servicios solo van en desarrollo)

# 4. Verificar que assets compilados existen
ls -lh Laravel_app/public/build/
ls -lh Vue_app/dist/
# Deben existir archivos .js y .css
```

---

## 🛠️ Comandos Útiles Post-Instalación

### Ver Logs

```bash
# Todos los servicios
docker compose logs -f

# Solo PHP (errores de Laravel)
docker compose logs -f php

# Solo Nginx (errores de conexión)
docker compose logs -f nginx

# Solo PostgreSQL
docker compose logs -f db
```

### Reiniciar Servicios

```bash
# Reiniciar todo
docker compose restart

# Reiniciar solo PHP (después de cambiar .env)
docker compose restart php

# Reiniciar solo Nginx (después de cambiar configuración)
docker compose restart nginx
```

### Limpiar Cachés

```bash
# Limpiar todos los caches de Laravel
docker compose exec -w /var/www/html/Laravel_app php php artisan cache:clear
docker compose exec -w /var/www/html/Laravel_app php php artisan config:clear
docker compose exec -w /var/www/html/Laravel_app php php artisan route:clear
docker compose exec -w /var/www/html/Laravel_app php php artisan view:clear

# Luego volver a cachear
docker compose exec -w /var/www/html/Laravel_app php php artisan config:cache
docker compose exec -w /var/www/html/Laravel_app php php artisan route:cache
docker compose exec -w /var/www/html/Laravel_app php php artisan view:cache
```

### Ejecutar Comandos Artisan

```bash
# Template
docker compose exec -w /var/www/html/Laravel_app php php artisan <comando>

# Ejemplos:
docker compose exec -w /var/www/html/Laravel_app php php artisan migrate
docker compose exec -w /var/www/html/Laravel_app php php artisan db:seed
docker compose exec -w /var/www/html/Laravel_app php php artisan tinker
docker compose exec -w /var/www/html/Laravel_app php php artisan queue:work
```

### Backup de Base de Datos

```bash
# Crear backup
docker compose exec -T db pg_dump -U postgres -d erp_production | gzip > backup_$(date +%Y%m%d_%H%M%S).sql.gz

# Restaurar backup
gunzip < backup_20260221_120000.sql.gz | docker compose exec -T db psql -U postgres -d erp_production
```

---

## 🔄 Actualizaciones Futuras

Cuando necesites actualizar el código:

```bash
# 1. Activar modo mantenimiento
docker compose exec -w /var/www/html/Laravel_app php php artisan down

# 2. Obtener últimos cambios
git pull origin main

# 3. Si hay cambios en dependencias
docker compose exec -w /var/www/html/Laravel_app php composer install --no-dev --optimize-autoloader

# 4. Si hay cambios en base de datos
docker compose exec -w /var/www/html/Laravel_app php php artisan migrate --force

# 5. Limpiar y cachear
docker compose exec -w /var/www/html/Laravel_app php php artisan config:clear
docker compose exec -w /var/www/html/Laravel_app php php artisan config:cache
docker compose exec -w /var/www/html/Laravel_app php php artisan route:cache
docker compose exec -w /var/www/html/Laravel_app php php artisan view:cache

# 6. Si hay cambios en assets, recompilar en local y subir, o:
# cd Laravel_app && npm ci && npm run build && rm -rf node_modules && cd ..

# 7. Reiniciar servicios
docker compose restart php nginx

# 8. Desactivar modo mantenimiento
docker compose exec -w /var/www/html/Laravel_app php php artisan up

# 9. Verificar
curl https://erp.tuempresa.com/health
```

---

## 🐛 Troubleshooting

### Problema: "502 Bad Gateway"

**Causa:** PHP-FPM no responde

```bash
# Ver logs de PHP
docker compose logs php --tail=50

# Reiniciar PHP
docker compose restart php

# Verificar que PHP está corriendo
docker compose exec php ps aux | grep php-fpm
```

### Problema: "404 Not Found" en rutas

**Causa:** Route cache desactualizado

```bash
docker compose exec -w /var/www/html/Laravel_app php php artisan route:clear
docker compose exec -w /var/www/html/Laravel_app php php artisan route:cache
docker compose restart nginx
```

### Problema: Assets no se cargan (CSS/JS)

**Causa:** Assets no compilados o ruta incorrecta

```bash
# Verificar que existen
ls -lh Laravel_app/public/build/

# Si no existen, compilar:
cd Laravel_app && npm ci && npm run build && cd ..

# Verificar permisos
docker compose exec php ls -lh /var/www/html/Laravel_app/public/build/

# Reiniciar Nginx
docker compose restart nginx
```

### Problema: "Connection refused" a base de datos

**Causa:** PostgreSQL no está listo o credenciales incorrectas

```bash
# Ver logs de DB
docker compose logs db --tail=50

# Verificar que DB está corriendo
docker compose ps db

# Probar conexión manual
docker compose exec db psql -U postgres -d erp_production -c "SELECT 1;"

# Verificar credenciales en .env
docker compose exec php cat /var/www/html/Laravel_app/.env | grep DB_
```

### Problema: Certificados SSL inválidos

```bash
# Verificar que existen
ls -lh docker/nginx/ssl/

# Verificar configuración Nginx
docker compose exec nginx nginx -t

# Ver logs de Nginx
docker compose logs nginx --tail=50

# Renovar certificados Let's Encrypt
sudo certbot renew
sudo cp /etc/letsencrypt/live/erp.tuempresa.com/fullchain.pem docker/nginx/ssl/certificate.crt
sudo cp /etc/letsencrypt/live/erp.tuempresa.com/privkey.pem docker/nginx/ssl/private.key
docker compose restart nginx
```

### Problema: "Out of memory" al compilar assets

**Solución:** Compilar en tu máquina local, no en el servidor

```bash
# En tu máquina local:
cd Laravel_app && npm install && npm run build && cd ..
cd Vue_app && npm install && npm run build && cd ..

# Subir solo los assets compilados al servidor
rsync -avz Laravel_app/public/build/ usuario@servidor:/ruta/proyecto/Laravel_app/public/build/
rsync -avz Vue_app/dist/ usuario@servidor:/ruta/proyecto/Vue_app/dist/
```

---

## 🔒 Seguridad en Producción

### Checklist de Seguridad

- [x] `APP_DEBUG=false` en `.env`
- [x] `APP_ENV=production` en `.env`
- [x] Contraseñas seguras en base de datos
- [x] No exponer puertos de PostgreSQL (5432)
- [x] No exponer puertos de Redis (6379)
- [x] HTTPS con certificados válidos
- [x] Firewall configurado (solo puertos 22, 80, 443)
- [x] Backups automáticos de base de datos
- [x] OPcache habilitado en PHP

### Configurar Firewall (UFW)

```bash
# Habilitar firewall
sudo ufw allow 22/tcp     # SSH
sudo ufw allow 80/tcp     # HTTP
sudo ufw allow 443/tcp    # HTTPS
sudo ufw enable

# Verificar estado
sudo ufw status
```

### Configurar Backups Automáticos

Crea el archivo `/root/backup-erp.sh`:

```bash
#!/bin/bash
BACKUP_DIR="/var/backups/erp"
DATE=$(date +%Y%m%d_%H%M%S)
PROJECT_DIR="/ruta/a/tu/proyecto"

mkdir -p $BACKUP_DIR

# Backup de base de datos
cd $PROJECT_DIR
docker compose exec -T db pg_dump -U postgres -d erp_production | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Mantener solo últimos 7 días
find $BACKUP_DIR -name "db_*.sql.gz" -mtime +7 -delete

echo "Backup completado: db_$DATE.sql.gz"
```

Hazlo ejecutable y agrégalo a cron:

```bash
chmod +x /root/backup-erp.sh

# Editar crontab
sudo crontab -e

# Agregar línea (backup diario a las 2 AM)
0 2 * * * /root/backup-erp.sh >> /var/log/backup-erp.log 2>&1
```

---

## 📊 Monitoreo

### Verificar Salud del Sistema

```bash
# Estado de contenedores
docker compose ps

# Uso de recursos
docker stats --no-stream

# Espacio en disco
df -h
docker system df

# Logs de aplicación
docker compose logs --tail=100

# Health check
curl https://erp.tuempresa.com/health
```

### Logs de Laravel

```bash
# Ver últimos logs
docker compose exec php tail -f /var/www/html/Laravel_app/storage/logs/laravel.log

# Buscar errores
docker compose exec php grep ERROR /var/www/html/Laravel_app/storage/logs/laravel.log
```

---

## 🏗️ Arquitectura del Proyecto

### Servicios Docker

| Servicio | Descripción | Puerto |
|----------|-------------|--------|
| **nginx** | Servidor web (reverse proxy) | 80, 443 |
| **php** | PHP 8.3 + FPM + Composer | - |
| **db** | PostgreSQL 16 | - (interno) |
| **redis** | Cache y sesiones | - (interno) |

**Nota:** En producción, los puertos de base de datos NO están expuestos al exterior por seguridad.

### Archivos de Configuración

```
docker-compose.base.yml   → Configuración base compartida
docker-compose.prod.yml   → Overrides para producción
docker/nginx/*.conf       → Configuraciones Nginx
Laravel_app/.env          → Variables de entorno Laravel
```

### Rutas Importantes

```
Laravel_app/
  ├── public/build/       → Assets compilados de Laravel (Vite)
  ├── storage/logs/       → Logs de aplicación
  ├── storage/app/        → Archivos subidos por usuarios
  └── .env                → Configuración

Vue_app/
  └── dist/               → Assets compilados de Vue

docker/
  └── nginx/
      ├── ssl/            → Certificados SSL
      ├── default.prod.conf  → Config Nginx producción
```

---

## 🆘 Soporte

### Información del Sistema

```bash
# Versiones instaladas
docker --version
docker compose version
php --version  # (dentro del contenedor)

# Estado general
docker compose ps
docker compose logs --tail=20

# Variables de entorno
docker compose exec php env | grep APP_
```

### Logs Completos para Debug

```bash
# Generar reporte completo
echo "=== Docker Compose Status ===" > debug.txt
docker compose ps >> debug.txt
echo -e "\n=== PHP Logs ===" >> debug.txt
docker compose logs php --tail=100 >> debug.txt
echo -e "\n=== Nginx Logs ===" >> debug.txt
docker compose logs nginx --tail=100 >> debug.txt
echo -e "\n=== Laravel Logs ===" >> debug.txt
docker compose exec php tail -100 /var/www/html/Laravel_app/storage/logs/laravel.log >> debug.txt

# Ver reporte
cat debug.txt
```

## 📝 Notas Importantes

1. **NO compiles assets en el servidor si tienes poca RAM** (< 2GB). Hazlo en local y sube solo los archivos compilados.

2. **Siempre usa modo mantenimiento** al actualizar:
   ```bash
   php artisan down
   # ... hacer cambios ...
   php artisan up
   ```

3. **Cachea después de cada cambio** en producción:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

4. **Backups antes de migraciones grandes**:
   ```bash
   docker compose exec -T db pg_dump -U postgres -d erp_production > backup_antes_de_cambio.sql
   ```
