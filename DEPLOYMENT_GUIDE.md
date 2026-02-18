# Guía de Deployment - ERP Filament

## Estrategia de Ambientes

Este proyecto usa **2 ramas principales** con ambientes separados:

| Rama | Ambiente | URL | Auto-Deploy |
|------|----------|-----|-------------|
| `develop` | Desarrollo | http://dev.erp.empresa.com:8090 | ✅ Automático |
| `main` | Producción | https://erp.empresa.com | ⚠️ Manual/Aprobación |

---

## Arquitectura de Docker Compose

### Archivos de Configuración

```
docker-compose.base.yml     → Configuración BASE compartida
docker-compose.dev.yml      → Overrides para DESARROLLO
docker-compose.prod.yml     → Overrides para PRODUCCIÓN
```

### Uso según ambiente

**Desarrollo (rama develop)**:
```bash
docker compose -f docker-compose.base.yml -f docker-compose.dev.yml --profile dev up -d
```

**Producción (rama main)**:
```bash
docker compose -f docker-compose.base.yml -f docker-compose.prod.yml up -d
```

---

## Diferencias entre Ambientes

### Servicios Activos

| Servicio | Desarrollo | Producción |
|----------|------------|------------|
| php | ✅ | ✅ |
| nginx | ✅ | ✅ |
| db | ✅ | ✅ |
| redis | ✅ | ✅ |
| **node** (Laravel Vite) | ✅ HMR | ❌ Assets pre-compilados |
| **vue** (Vue.js Vite) | ✅ HMR | ❌ Assets pre-compilados |
| **mailpit** | ✅ Email testing | ❌ SMTP real |

### Variables de Entorno Clave

| Variable | Desarrollo | Producción |
|----------|------------|------------|
| `APP_ENV` | local | production |
| `APP_DEBUG` | true | false |
| `PHP_OPCACHE_ENABLE` | 0 | 1 |
| `PHP_OPCACHE_VALIDATE_TIMESTAMPS` | 1 | 0 |
| `MAIL_MAILER` | smtp (mailpit) | smtp (real) |
| `LOG_LEVEL` | debug | error |

### Puertos Expuestos

**Desarrollo**:
- 8090 → Laravel
- 8081 → Vue.js
- 5173 → Vite Laravel (HMR)
- 5174 → Vite Vue (HMR)
- 8025 → Mailpit Web UI
- 5432 → PostgreSQL (para pgAdmin)
- 6379 → Redis (para Redis Commander)

**Producción**:
- 80 → HTTP (redirige a HTTPS)
- 443 → HTTPS
- *No se exponen puertos de bases de datos*

---

## Setup Inicial

### 1. Servidor de Desarrollo

```bash
# En el servidor de desarrollo
git clone <repo> erp-dev
cd erp-dev
git checkout develop

# Copiar variables de entorno
cp Laravel_app/.env.example Laravel_app/.env

# Editar .env con credenciales de desarrollo
nano Laravel_app/.env

# Iniciar servicios
docker compose -f docker-compose.base.yml -f docker-compose.dev.yml --profile dev up -d

# Esperar a que servicios estén listos
sleep 20

# Instalar dependencias
docker compose exec -w /var/www/html/Laravel_app php composer install

# Generar clave
docker compose exec -w /var/www/html/Laravel_app php php artisan key:generate

# Migrar base de datos
docker compose exec -w /var/www/html/Laravel_app php php artisan migrate:fresh --seed

# Crear super admin
docker compose exec -w /var/www/html/Laravel_app php php artisan shield:super-admin

# Verificar salud
docker compose ps
```

### 2. Servidor de Producción

```bash
# En el servidor de producción
git clone <repo> erp-prod
cd erp-prod
git checkout main

# Copiar template de producción
cp .env.production.example Laravel_app/.env

# ⚠️ IMPORTANTE: Editar .env con credenciales REALES de producción
nano Laravel_app/.env

# Compilar assets en local o CI/CD (NO en servidor)
# Opción A: Local
cd Laravel_app && npm install && npm run build && cd ..
cd Vue_app && npm install && npm run build && cd ..

# Opción B: GitHub Actions lo hace automáticamente

# Crear directorio para certificados SSL
mkdir -p docker/nginx/ssl

# Copiar certificados SSL (Let's Encrypt o comprados)
# cp /etc/letsencrypt/live/tudominio.com/fullchain.pem docker/nginx/ssl/certificate.crt
# cp /etc/letsencrypt/live/tudominio.com/privkey.pem docker/nginx/ssl/private.key

# Iniciar servicios
docker compose -f docker-compose.base.yml -f docker-compose.prod.yml up -d

# Esperar a que servicios estén listos
sleep 30

# Instalar dependencias (sin dev)
docker compose exec -w /var/www/html/Laravel_app php composer install --no-dev --optimize-autoloader

# Generar clave
docker compose exec -w /var/www/html/Laravel_app php php artisan key:generate

# Migrar base de datos
docker compose exec -w /var/www/html/Laravel_app php php artisan migrate --force

# Cachear configuraciones
docker compose exec -w /var/www/html/Laravel_app php php artisan config:cache
docker compose exec -w /var/www/html/Laravel_app php php artisan route:cache
docker compose exec -w /var/www/html/Laravel_app php php artisan view:cache

# Generar permisos Shield
docker compose exec -w /var/www/html/Laravel_app php php artisan shield:generate --all

# Crear super admin
docker compose exec -w /var/www/html/Laravel_app php php artisan shield:super-admin

# Verificar salud
docker compose ps
curl https://tudominio.com/health
```

---

## Workflow de CI/CD

### GitHub Actions

Se crearon **2 workflows**:

1. **`.github/workflows/develop.yml`** → Auto-deploy a DEV
2. **`.github/workflows/production.yml`** → Deploy a PROD (con aprobación)

### Flujo de Trabajo

```
┌─────────────┐
│ Developer   │
│ commit code │
└──────┬──────┘
       │
       ├─ Push a develop ─────────────────────┐
       │                                       │
       │                            ┌──────────▼──────────┐
       │                            │ GitHub Actions      │
       │                            │ - Run tests         │
       │                            │ - Build containers  │
       │                            │ - PHPUnit           │
       │                            └──────────┬──────────┘
       │                                       │
       │                                 ✅ Tests pass
       │                                       │
       │                            ┌──────────▼──────────┐
       │                            │ Auto-Deploy to DEV  │
       │                            │ - Pull code         │
       │                            │ - Restart services  │
       │                            │ - Run migrations    │
       │                            └─────────────────────┘
       │
       │
       ├─ Create PR develop → main ───────────┐
       │                                       │
       │                              ┌────────▼────────┐
       │                              │ Code Review     │
       │                              │ QA Testing      │
       │                              └────────┬────────┘
       │                                       │
       │                                  Approved
       │                                       │
       └─ Merge to main ──────────────────────┤
                                               │
                                    ┌──────────▼──────────┐
                                    │ GitHub Actions      │
                                    │ - Run tests         │
                                    │ - Build assets      │
                                    │ - Security checks   │
                                    └──────────┬──────────┘
                                               │
                                          ✅ Success
                                               │
                                    ┌──────────▼──────────┐
                                    │ Deploy to PROD      │
                                    │ - Maintenance mode  │
                                    │ - Pull code         │
                                    │ - Upload assets     │
                                    │ - Run migrations    │
                                    │ - Cache configs     │
                                    │ - Health check      │
                                    │ - Exit maintenance  │
                                    └─────────────────────┘
```

---

## Secrets de GitHub

Debes configurar estos secrets en GitHub:

### Para Desarrollo (develop)

```
DEV_SSH_HOST        → IP del servidor de desarrollo
DEV_SSH_USER        → Usuario SSH (ej: ubuntu)
DEV_SSH_KEY         → Private key SSH
DEV_SSH_PORT        → Puerto SSH (default: 22)
DEV_APP_PATH        → Ruta del proyecto (ej: /home/ubuntu/erp-dev)
```

### Para Producción (main)

```
PROD_SSH_HOST       → IP del servidor de producción
PROD_SSH_USER       → Usuario SSH
PROD_SSH_KEY        → Private key SSH
PROD_SSH_PORT       → Puerto SSH (default: 22)
PROD_APP_PATH       → Ruta del proyecto (ej: /var/www/erp-prod)
PROD_DOMAIN         → Dominio (ej: erp.tuempresa.com)
```

### Cómo agregar secrets

```bash
# En GitHub:
# Settings → Secrets and variables → Actions → New repository secret

# O con GitHub CLI:
gh secret set DEV_SSH_HOST -b"192.168.1.100"
gh secret set DEV_SSH_USER -b"ubuntu"
gh secret set DEV_SSH_KEY < ~/.ssh/id_rsa
```

---

## Comandos Útiles (Makefile)

Se incluye un `Makefile` con atajos:

### Desarrollo

```bash
# Iniciar desarrollo
make dev

# Ver logs
make dev-logs

# Detener
make dev-stop

# Ejecutar tests
make test

# Limpiar caches
make cache-clear
```

### Producción

```bash
# Iniciar producción
make prod

# Ver logs
make prod-logs

# Optimizar aplicación
make optimize

# Backup de DB
make db-backup

# Verificar salud
make health
```

### Laravel

```bash
# Migrar
make migrate

# Reset DB
make fresh

# Instalar dependencias
make install

# Compilar assets
make assets-build
```

Ver todos los comandos: `make help`

---

## Troubleshooting

### Problema: Assets no se cargan en producción

**Causa**: Servicios node/vue están corriendo (no deberían)

**Solución**:
```bash
# Detener servicios innecesarios
docker compose -f docker-compose.base.yml -f docker-compose.prod.yml down

# Compilar assets localmente
cd Laravel_app && npm run build
cd ../Vue_app && npm run build

# Reiniciar solo servicios necesarios
docker compose -f docker-compose.base.yml -f docker-compose.prod.yml up -d
```

### Problema: 502 Bad Gateway en producción

**Causa**: PHP-FPM no está corriendo correctamente

**Diagnóstico**:
```bash
# Ver logs de PHP
docker compose logs php

# Ver estado de contenedores
docker compose ps

# Verificar conectividad Nginx → PHP
docker compose exec nginx ping php
```

**Solución**:
```bash
# Reiniciar PHP
docker compose restart php

# Si persiste, reconstruir
docker compose -f docker-compose.base.yml -f docker-compose.prod.yml build php --no-cache
docker compose restart php
```

### Problema: Migraciones fallan en deploy

**Causa**: Base de datos no está lista cuando se ejecutan migraciones

**Solución**: Agregar healthcheck en docker-compose.prod.yml (ya incluido)

```yaml
db:
  healthcheck:
    test: ["CMD-SHELL", "pg_isready -U ${POSTGRES_USER} -d ${POSTGRES_DB}"]
    interval: 10s
    timeout: 5s
    retries: 5
```

### Problema: Certificados SSL no válidos

**Solución con Let's Encrypt**:
```bash
# Instalar certbot
sudo apt install certbot

# Obtener certificado
sudo certbot certonly --standalone -d erp.tuempresa.com

# Copiar a proyecto
sudo cp /etc/letsencrypt/live/erp.tuempresa.com/fullchain.pem docker/nginx/ssl/certificate.crt
sudo cp /etc/letsencrypt/live/erp.tuempresa.com/privkey.pem docker/nginx/ssl/private.key
sudo chown 1000:1000 docker/nginx/ssl/*

# Reiniciar Nginx
docker compose restart nginx
```

---

## Seguridad en Producción

### Checklist

- [ ] `APP_DEBUG=false` en producción
- [ ] Cambiar `APP_KEY` único por ambiente
- [ ] Usar contraseñas seguras para PostgreSQL
- [ ] No exponer puertos de base de datos (5432, 6379)
- [ ] Configurar firewall (ufw/iptables)
- [ ] Usar HTTPS con certificados válidos
- [ ] Limitar intentos de login (Laravel Fortify)
- [ ] Habilitar logs de auditoría
- [ ] Backups automáticos de base de datos
- [ ] Monitoreo de recursos (CPU, RAM, disco)

### Configurar Firewall

```bash
# Ubuntu/Debian
sudo ufw allow 22/tcp    # SSH
sudo ufw allow 80/tcp    # HTTP
sudo ufw allow 443/tcp   # HTTPS
sudo ufw enable

# Verificar
sudo ufw status
```

---

## Monitoreo

### Health Checks

**Endpoint de salud**:
```
GET https://erp.tuempresa.com/health
```

Respuesta esperada:
```
healthy
```

### Logs

**Ver logs en tiempo real**:
```bash
# Todos los servicios
docker compose logs -f

# Solo PHP
docker compose logs -f php

# Solo Nginx
docker compose logs -f nginx

# Últimos 100 logs
docker compose logs --tail=100 php
```

### Métricas

**Uso de recursos**:
```bash
# Ver CPU/RAM por contenedor
docker stats

# Ver espacio en disco
docker system df

# Ver volúmenes
docker volume ls
```

---

## Rollback

### Rollback Rápido en Producción

```bash
# SSH al servidor
ssh user@prod-server
cd /var/www/erp-prod

# Modo mantenimiento
docker compose exec -T -w /var/www/html/Laravel_app php php artisan down

# Volver al commit anterior
git reset --hard HEAD~1

# Reiniciar servicios
docker compose restart php nginx

# Salir de mantenimiento
docker compose exec -T -w /var/www/html/Laravel_app php php artisan up
```

### Rollback Automático

El workflow de producción (`production.yml`) incluye rollback automático si:
- Los tests fallan
- El deploy falla
- El health check falla

---

## Backups

### Backup Automático de Base de Datos

**Crear script** `/root/backup-db.sh`:
```bash
#!/bin/bash
BACKUP_DIR="/var/backups/erp"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="erp_production"

mkdir -p $BACKUP_DIR

docker compose -f /var/www/erp-prod/docker-compose.base.yml -f /var/www/erp-prod/docker-compose.prod.yml \
  exec -T db pg_dump -U postgres -d $DB_NAME | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Mantener solo últimos 7 días
find $BACKUP_DIR -name "db_*.sql.gz" -mtime +7 -delete

echo "Backup completado: db_$DATE.sql.gz"
```

**Automatizar con cron**:
```bash
sudo crontab -e

# Agregar línea (backup diario a las 2 AM)
0 2 * * * /root/backup-db.sh >> /var/log/backup-db.log 2>&1
```

---

## Próximos Pasos

1. **Configurar SSL** con Let's Encrypt
2. **Agregar monitoreo** con Prometheus + Grafana
3. **CI/CD avanzado** con tests de integración
4. **Docker Swarm** para alta disponibilidad
5. **CDN** para assets estáticos (CloudFlare, AWS CloudFront)
6. **Backups offsite** (S3, Dropbox, rsync)

---

## Contacto

Para soporte: devops@tuempresa.com

**Recuerda**: Siempre testear en desarrollo antes de desplegar a producción 🚀
