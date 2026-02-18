# Guía Rápida: Deployment Multi-Ambiente

## 📋 Resumen de Cambios

Se han creado/modificado los siguientes archivos para soportar **ambientes separados** (desarrollo y producción):

### Archivos Creados

```
📁 Raíz del proyecto
├── docker-compose.base.yml          → Configuración base compartida
├── docker-compose.dev.yml           → Overrides para desarrollo (rama develop)
├── docker-compose.prod.yml          → Overrides para producción (rama main)
├── .env.production.example          → Template de variables para producción
├── Makefile                         → Comandos rápidos (make dev, make prod, etc.)
└── DEPLOYMENT_GUIDE.md              → Guía completa de deployment

📁 docker/nginx/
└── default.prod.conf                → Configuración Nginx para producción (HTTPS)

📁 .github/workflows/
├── develop.yml                      → CI/CD para rama develop (auto-deploy)
└── production.yml                   → CI/CD para rama main (con aprobaciones)
```

---

## 🚀 Uso Rápido

### Desarrollo Local (rama develop)

```bash
# Iniciar servicios con HMR (Hot Module Replacement)
docker compose -f docker-compose.base.yml -f docker-compose.dev.yml --profile dev up -d

# O usando Makefile (más fácil)
make dev

# Ver logs
make dev-logs

# Detener
make dev-stop
```

**URLs de desarrollo:**
- Laravel/Filament: http://localhost:8090
- Vue.js: http://localhost:8081
- Mailpit (email testing): http://localhost:8025
- PostgreSQL: localhost:5432 (para pgAdmin/DBeaver)

---

### Producción (rama main)

```bash
# Compilar assets PRIMERO (en local o CI/CD)
cd Laravel_app && npm run build
cd ../Vue_app && npm run build

# Iniciar servicios (sin node/vue)
docker compose -f docker-compose.base.yml -f docker-compose.prod.yml up -d

# O usando Makefile
make prod

# Ver logs
make prod-logs

# Optimizar aplicación
make optimize
```

**URLs de producción:**
- HTTP: http://tu-dominio.com (redirige a HTTPS)
- HTTPS: https://tu-dominio.com

---

## 🔑 Diferencias Clave por Ambiente

| Aspecto | Desarrollo (develop) | Producción (main) |
|---------|---------------------|-------------------|
| **Compilación assets** | ✅ En tiempo real (Vite HMR) | ❌ Pre-compilados con `npm run build` |
| **Servicios node/vue** | ✅ Activos | ❌ Deshabilitados |
| **Mailpit** | ✅ Email testing | ❌ SMTP real |
| **DEBUG** | `APP_DEBUG=true` | `APP_DEBUG=false` |
| **PHP OPcache** | Desactivado | Activado |
| **Puertos DB** | Expuestos (5432, 6379) | No expuestos |
| **SSL** | Sin SSL | HTTPS con certificados |
| **Logs** | Verbose (debug) | Solo errores |

---

## 📦 Setup Inicial

### 1. En GitHub (Configurar Secrets)

Ve a: **Settings → Secrets and variables → Actions → New repository secret**

**Para desarrollo:**
```
DEV_SSH_HOST=192.168.1.100
DEV_SSH_USER=ubuntu
DEV_SSH_KEY=<tu-private-key-ssh>
DEV_SSH_PORT=22
DEV_APP_PATH=/home/ubuntu/erp-dev
```

**Para producción:**
```
PROD_SSH_HOST=tu-servidor-prod.com
PROD_SSH_USER=ubuntu
PROD_SSH_KEY=<tu-private-key-ssh>
PROD_SSH_PORT=22
PROD_APP_PATH=/var/www/erp-prod
PROD_DOMAIN=erp.tuempresa.com
```

---

### 2. En Servidor de Desarrollo

```bash
# Clonar repo
git clone <your-repo> erp-dev
cd erp-dev
git checkout develop

# Copiar .env
cp Laravel_app/.env.example Laravel_app/.env
nano Laravel_app/.env  # Editar credenciales

# Iniciar con Makefile
make initial-setup

# O manualmente:
docker compose -f docker-compose.base.yml -f docker-compose.dev.yml --profile dev up -d
sleep 20
docker compose exec -w /var/www/html/Laravel_app php composer install
docker compose exec -w /var/www/html/Laravel_app php php artisan key:generate
docker compose exec -w /var/www/html/Laravel_app php php artisan migrate:fresh --seed
docker compose exec -w /var/www/html/Laravel_app php php artisan shield:super-admin
```

---

### 3. En Servidor de Producción

```bash
# Clonar repo
git clone <your-repo> erp-prod
cd erp-prod
git checkout main

# Copiar template de producción
cp .env.production.example Laravel_app/.env
nano Laravel_app/.env  # ⚠️ Configurar credenciales REALES

# Compilar assets (hacer en local o CI/CD los subirá)
cd Laravel_app && npm install && npm run build && cd ..
cd Vue_app && npm install && npm run build && cd ..

# Configurar SSL (Let's Encrypt)
sudo certbot certonly --standalone -d erp.tuempresa.com
sudo cp /etc/letsencrypt/live/erp.tuempresa.com/fullchain.pem docker/nginx/ssl/certificate.crt
sudo cp /etc/letsencrypt/live/erp.tuempresa.com/privkey.pem docker/nginx/ssl/private.key
sudo chown 1000:1000 docker/nginx/ssl/*

# Iniciar servicios
docker compose -f docker-compose.base.yml -f docker-compose.prod.yml up -d
sleep 30

# Setup Laravel
docker compose exec -w /var/www/html/Laravel_app php composer install --no-dev --optimize-autoloader
docker compose exec -w /var/www/html/Laravel_app php php artisan key:generate
docker compose exec -w /var/www/html/Laravel_app php php artisan migrate --force
docker compose exec -w /var/www/html/Laravel_app php php artisan config:cache
docker compose exec -w /var/www/html/Laravel_app php php artisan route:cache
docker compose exec -w /var/www/html/Laravel_app php php artisan view:cache
docker compose exec -w /var/www/html/Laravel_app php php artisan shield:super-admin

# Verificar
curl https://erp.tuempresa.com/health
```

---

## 🔄 Workflow de CI/CD

### Flujo Automático

```
Developer → Push a develop
    ↓
GitHub Actions:
  ✓ Run tests
  ✓ Build containers
  ✓ PHPUnit
    ↓
  ✅ Auto-Deploy to DEV
    ↓
DEV Server Updated 🎉


Developer → Create PR (develop → main)
    ↓
Code Review + QA Testing
    ↓
Merge to main
    ↓
GitHub Actions:
  ✓ Run tests
  ✓ Build assets (Laravel + Vue)
  ✓ Security checks
    ↓
  ⚠️ Manual Approval (opcional)
    ↓
  ✅ Deploy to PROD
    ↓
PROD Server Updated 🚀
```

### Deploy Manual

**A desarrollo:**
```bash
make deploy-dev
# o
git push origin develop
```

**A producción:**
```bash
make deploy-prod
# o
git push origin main
```

---

## 🛠️ Comandos Makefile

Ver todos los comandos disponibles:
```bash
make help
```

### Más usados

```bash
# Desarrollo
make dev              # Iniciar desarrollo
make dev-logs         # Ver logs
make dev-stop         # Detener

# Producción
make prod             # Iniciar producción
make prod-logs        # Ver logs
make optimize         # Optimizar aplicación

# Laravel
make migrate          # Ejecutar migraciones
make fresh            # Reset DB + seed
make test             # PHPUnit
make cache-clear      # Limpiar caches
make cache            # Cachear configs

# Assets
make assets-dev       # Compilar dev (HMR)
make assets-build     # Compilar producción

# Database
make db-backup        # Backup PostgreSQL
make db-connect       # Conectar a psql

# Docker
make logs             # Ver logs
make restart          # Reiniciar servicios
make health           # Verificar salud
make clean            # Limpiar todo
```

---

## 🔧 Modificar Configuración

### Cambiar puertos

**Desarrollo** (`docker-compose.dev.yml`):
```yaml
nginx:
  ports:
    - "8090:80"    # Cambiar 8090 por tu puerto
    - "8081:8081"
```

**Producción** (`docker-compose.prod.yml`):
```yaml
nginx:
  ports:
    - "80:80"      # HTTP
    - "443:443"    # HTTPS
```

### Agregar replicas PHP (load balancing)

En `docker-compose.prod.yml`:
```yaml
php:
  deploy:
    replicas: 3  # Cambiar de 2 a 3 instancias
```

### Cambiar límites de recursos

En `docker-compose.prod.yml`:
```yaml
php:
  deploy:
    resources:
      limits:
        cpus: "2"      # Aumentar CPUs
        memory: 2048M  # Aumentar RAM
```

---

## 🐛 Troubleshooting

### Problema: Assets no cargan en producción

```bash
# Verificar que assets estén compilados
ls -la Laravel_app/public/build/
ls -la Vue_app/dist/

# Si no existen, compilar:
cd Laravel_app && npm run build
cd ../Vue_app && npm run build
```

### Problema: 502 Bad Gateway

```bash
# Ver logs de PHP
docker compose logs php

# Reiniciar PHP
docker compose restart php

# Verificar conectividad
docker compose exec nginx ping php
```

### Problema: Migraciones fallan

```bash
# Esperar a que PostgreSQL esté listo
docker compose exec db pg_isready -U postgres

# Ejecutar manualmente
docker compose exec -w /var/www/html/Laravel_app php php artisan migrate --force
```

### Problema: Permisos en storage/

```bash
# Arreglar permisos
sudo chown -R 1000:1000 Laravel_app/storage Laravel_app/bootstrap/cache
sudo chmod -R 775 Laravel_app/storage Laravel_app/bootstrap/cache
```

---

## 📚 Documentación Completa

- **[DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md)** → Guía completa de deployment
- **[DOCKER_ARCHITECTURE_DOCUMENTATION.md](DOCKER_ARCHITECTURE_DOCUMENTATION.md)** → Arquitectura Docker explicada

---

## ✅ Checklist de Producción

Antes de desplegar a producción, verificar:

- [ ] `APP_DEBUG=false` en Laravel_app/.env
- [ ] `APP_KEY` único generado
- [ ] Contraseñas seguras en PostgreSQL
- [ ] Assets compilados (`npm run build`)
- [ ] Certificados SSL configurados
- [ ] Firewall configurado (80, 443, 22)
- [ ] Backups automáticos programados
- [ ] Secrets de GitHub configurados
- [ ] URL Health check responde
- [ ] Mailpit deshabilitado (usar SMTP real)

---

## 🎯 Próximos Pasos

1. Configurar secrets en GitHub
2. Hacer push a `develop` para testear CI/CD
3. Configurar certificados SSL en producción
4. Programar backups automáticos
5. Configurar monitoreo (Prometheus/Grafana)

---

## 📞 Soporte

Para preguntas o problemas, consultar:
- [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md) (guía detallada)
- [DOCKER_ARCHITECTURE_DOCUMENTATION.md](DOCKER_ARCHITECTURE_DOCUMENTATION.md) (arquitectura)
- GitHub Issues: <your-repo-url>/issues

Happy deploying! 🚀
