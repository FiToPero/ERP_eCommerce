# 🚀 Guía Completa de CI/CD con GitHub Actions

Sistema de Integración y Despliegue Continuo (CI/CD) para ERP Filament con dos ambientes: **Desarrollo** y **Producción**.

---

## 📚 Índice

1. [¿Qué es CI/CD?](#-qué-es-cicd)
2. [Arquitectura del Sistema](#-arquitectura-del-sistema)
3. [Cómo Funciona GitHub Actions](#-cómo-funciona-github-actions)
4. [Flujo de Trabajo (Workflow)](#-flujo-de-trabajo-workflow)
5. [Configuración Inicial](#-configuración-inicial)
6. [Guía de Desarrollo Diario](#-guía-de-desarrollo-diario)
7. [Workflows Explicados](#-workflows-explicados)
8. [Gestión de Ramas](#-gestión-de-ramas)
9. [Secretos y Variables](#-secretos-y-variables)
10. [Monitoreo y Logs](#-monitoreo-y-logs)
11. [Troubleshooting](#-troubleshooting)
12. [Mejores Prácticas](#-mejores-prácticas)

---

## 🎯 ¿Qué es CI/CD?

### CI - Continuous Integration (Integración Continua)

**Integración Continua** es la práctica de fusionar cambios de código en el repositorio principal de forma frecuente, ejecutando pruebas automáticas cada vez.

**Beneficios:**
- ✅ Detecta errores temprano
- ✅ Reduce conflictos de código
- ✅ Mantiene el código siempre funcional
- ✅ Facilita la colaboración del equipo

### CD - Continuous Deployment (Despliegue Continuo)

**Despliegue Continuo** es la práctica de automatizar el proceso de llevar el código a producción después de pasar todas las pruebas.

**Beneficios:**
- 🚀 Deploys rápidos y predecibles
- 🚀 Menos errores humanos
- 🚀 Rollback rápido en caso de problemas
- 🚀 Entregas más frecuentes

### GitHub Actions

**GitHub Actions** es la plataforma de CI/CD integrada en GitHub que permite automatizar tareas mediante "workflows" (flujos de trabajo).

**Componentes clave:**
- **Workflow**: Archivo YAML que define el proceso automatizado
- **Job**: Grupo de pasos que se ejecutan en una máquina
- **Step**: Acción individual (comando o script)
- **Runner**: Máquina virtual donde se ejecutan los jobs
- **Secrets**: Variables sensibles cifradas (credenciales, tokens)

---

## 🏗️ Arquitectura del Sistema

```
┌─────────────────────────────────────────────────────────────┐
│                      DESARROLLADOR                           │
│  (Tu máquina local - feature/nueva-funcionalidad)           │
└────────────────────┬────────────────────────────────────────┘
                     │ git push
                     ▼
┌─────────────────────────────────────────────────────────────┐
│                    GITHUB REPOSITORY                         │
│                                                               │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐        │
│  │   develop   │  │  feature/*  │  │     main    │        │
│  │   (beta)    │  │  (trabajo)  │  │ (estable)   │        │
│  └──────┬──────┘  └─────────────┘  └──────┬──────┘        │
│         │                                   │                │
│         │ Push/Merge                       │ Merge PR       │
│         ▼                                   ▼                │
│  ┌─────────────────┐              ┌─────────────────┐      │
│  │ develop.yml     │              │ production.yml  │      │
│  │ (Auto-deploy)   │              │ (Auto-deploy)   │      │
│  └────────┬────────┘              └────────┬────────┘      │
└───────────┼──────────────────────────────────┼──────────────┘
            │                                   │
            │ via SSH                          │ via SSH
            ▼                                   ▼
┌─────────────────────┐              ┌─────────────────────┐
│  SERVIDOR DESARROLLO │              │  SERVIDOR PRODUCCIÓN │
│                      │              │                      │
│  🖥️  dev.example.com │              │  🌐 erp.empresa.com  │
│                      │              │                      │
│  • Testing rápido    │              │  • Alta estabilidad  │
│  • Debug habilitado  │              │  • Debug OFF         │
│  • Cambios frecuentes│              │  • Cambios revisados │
│  • Sin SSL requerido │              │  • SSL obligatorio   │
└─────────────────────┘              └─────────────────────┘
```

---

## ⚙️ Cómo Funciona GitHub Actions

### 1. Evento Disparador (Trigger)

Cuando ocurre un evento en GitHub:

```yaml
on:
  push:
    branches: [ "develop" ]  # Al hacer push a develop
  pull_request:
    branches: [ "main" ]     # Al abrir PR a main
```

### 2. Runner Inicia

GitHub inicia una máquina virtual Ubuntu con Docker instalado.

### 3. Ejecución de Jobs

Se ejecutan los jobs definidos en secuencia o paralelo:

```yaml
jobs:
  test:        # Job 1: Ejecutar pruebas
  build:       # Job 2: Compilar assets (después de test)
  deploy:      # Job 3: Desplegar (después de build)
```

### 4. Conexión al Servidor

GitHub Actions se conecta a tu servidor vía SSH y ejecuta comandos:

```yaml
- uses: appleboy/ssh-action@v1.1.0
  with:
    host: ${{ secrets.DEV_SSH_HOST }}
    username: ${{ secrets.DEV_SSH_USER }}
    key: ${{ secrets.DEV_SSH_KEY }}
```

### 5. Despliegue Automatizado

Se ejecutan los comandos en el servidor remoto: pull, build, migrate, cache, restart.

## 🔄 Flujo de Trabajo (Workflow)

### Flujo Completo de Desarrollo

```
1. DESARROLLO LOCAL
   ├─ Creas rama: git checkout -b feature/nueva-funcionalidad
   ├─ Codificas y testas localmente
   ├─ Commit: git commit -m "feat: agregar nueva funcionalidad"
   └─ Push: git push origin feature/nueva-funcionalidad
                │
                ▼
2. PULL REQUEST A DEVELOP
   ├─ Abres PR en GitHub: feature/nueva-funcionalidad → develop
   ├─ ⚡ GitHub Actions ejecuta tests automáticos (sobre el código del PR)
   ├─ Code review del equipo
   ├─ Apruebas y haces merge (botón "Merge pull request")
   └─ Borras la rama feature/*
                │
                ▼ (El merge a develop es un push automático)
3. AUTO-DEPLOY A DESARROLLO (⚡ Se dispara automáticamente al hacer merge)
   ├─ GitHub detecta push a rama develop
   ├─ Se ejecuta workflow: .github/workflows/develop.yml
   ├─ ⚡ Re-ejecuta tests en ambiente dev
   ├─ 🚀 Deploy automático al servidor dev via SSH
   ├─ Pull código, rebuild, migrate, cache
   └─ ✅ Equipo prueba en dev.example.com
                │
                ▼
4. PULL REQUEST A MAIN (PRODUCCIÓN)
   ├─ Después de probar en dev
   ├─ Abres PR: develop → main
   ├─ ⚡ GitHub Actions ejecuta tests de producción (sobre el código del PR)
   ├─ Code review más estricto (2 aprobaciones recomendadas)
   ├─ Aprueba el Product Owner / Tech Lead
   └─ Merge a main (botón "Merge pull request")
                │
                ▼ (El merge a main es un push automático)
5. AUTO-DEPLOY A PRODUCCIÓN (⚡ Se dispara automáticamente al hacer merge)
   ├─ GitHub detecta push a rama main
   ├─ Se ejecuta workflow: .github/workflows/production.yml
   ├─ ⚡ Ejecuta tests completos en ambiente prod
   ├─ 📦 Compila assets optimizados (Laravel + Vue)
   ├─ 🚀 Deploy al servidor de producción via SSH
   ├─ App en modo mantenimiento durante deploy (php artisan down)
   ├─ Pull código, rebuild, migrate, cache, copiar assets
   ├─ Reinicia servicios y desactiva mantenimiento (php artisan up)
   └─ ✅ Verificación post-deploy automática
                │
                ▼
6. MONITOREO Y ROLLBACK
   ├─ Monitoreas logs y métricas
   ├─ Si hay problemas: git revert o rollback manual
   └─ Si todo OK: seguir con siguientes features
```

### ⚡ Importante: Deploys Automáticos

**Los deploys son 100% automáticos:**

- **Merge a `develop`** → Dispara automáticamente `develop.yml` → Deploy a servidor DEV
- **Merge a `main`** → Dispara automáticamente `production.yml` → Deploy a servidor PRODUCCIÓN

**No necesitas:**
- Ejecutar comandos manualmente
- Conectarte al servidor
- Correr scripts de deploy

**Solo necesitas:**
1. Hacer merge del Pull Request en GitHub
2. Esperar a que GitHub Actions termine (ver tab "Actions")
3. Verificar que el deploy fue exitoso ✅

**Tiempo estimado:**
- Deploy a DEV: ~10 minutos desde el merge
- Deploy a PRODUCCIÓN: ~20 minutos desde el merge

### ❓ Preguntas Frecuentes sobre Deploys Automáticos

**P: ¿Necesito conectarme al servidor después del merge?**  
R: No. GitHub Actions se conecta automáticamente via SSH y ejecuta todos los comandos.

**P: ¿Qué pasa si el deploy falla?**  
R: GitHub Actions se detiene, no aplica cambios y envía notificación de error. El servidor mantiene la versión anterior funcionando.

**P: ¿Puedo cancelar un deploy en progreso?**  
R: Sí, en GitHub → Actions → Click en el workflow corriendo → "Cancel workflow".

**P: ¿El deploy hace downtime?**  
R: 
- **DEV:** No, se reinicia PHP pero nginx sigue sirviendo.
- **PROD:** Sí, ~30 segundos con `php artisan down` (página de mantenimiento).

**P: ¿Puedo hacer deploy manual sin merge?**  
R: Sí, editando el workflow para agregar `workflow_dispatch` (permite ejecutar manualmente desde GitHub).

**P: ¿Los tests se ejecutan dos veces?**  
R: Sí:
1. Al abrir el PR (para validar el código)
2. Al hacer merge (en el ambiente de deploy)

**P: ¿Se puede revertir un deploy?**  
R: Sí, usando `git revert` y haciendo merge del revert, o rollback manual en servidor.

---

## 🛠️ Configuración Inicial

### Paso 1: Preparar Servidores

#### Servidor de Desarrollo

```bash
# Conectar via SSH
ssh usuario@dev.example.com

# Clonar repositorio
cd /var/www/
git clone git@github.com:tuempresa/erp-filament.git
cd erp-filament

# Configurar rama develop
git checkout develop

# Crear .env de desarrollo
cp .env.example .env
nano .env

# Contenido del .env:
APP_ENV=development
APP_DEBUG=true
APP_URL=http://dev.example.com

DB_CONNECTION=pgsql
DB_HOST=db
DB_DATABASE=erp_development
DB_USERNAME=postgres
DB_PASSWORD=tu_password_dev

# Configurar Docker
docker compose -f docker-compose.base.yml -f docker-compose.dev.yml --profile dev up -d
```

#### Servidor de Producción

```bash
# Conectar via SSH
ssh usuario@erp.empresa.com

# Clonar repositorio
cd /var/www/
git clone git@github.com:tuempresa/erp-filament.git
cd erp-filament

# Asegurarse de estar en main
git checkout main

# Crear .env de producción
cp .env.example .env
nano .env

# Contenido del .env:
APP_ENV=production
APP_DEBUG=false
APP_URL=https://erp.empresa.com

DB_CONNECTION=pgsql
DB_HOST=db
DB_DATABASE=erp_production
DB_USERNAME=postgres
DB_PASSWORD=tu_password_seguro_prod

# Configurar Docker
docker compose -f docker-compose.base.yml -f docker-compose.prod.yml up -d
```

### Paso 2: Configurar SSH para GitHub Actions

En **cada servidor**, genera una clave SSH para GitHub Actions:

```bash
# En el servidor
ssh-keygen -t ed25519 -C "github-actions@tuempresa.com" -f ~/.ssh/github_actions
cat ~/.ssh/github_actions      # Clave PRIVADA (para GitHub Secrets)
cat ~/.ssh/github_actions.pub  # Clave pública (agregar a authorized_keys)

# Agregar clave pública a authorized_keys
cat ~/.ssh/github_actions.pub >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys
```

**Importante:** La clave **PRIVADA** se guarda en GitHub Secrets, la **PÚBLICA** queda en el servidor.

### Paso 3: Configurar GitHub Secrets

Ve a tu repositorio en GitHub: **Settings → Secrets and variables → Actions → New repository secret**

#### Secrets para Desarrollo

| Secret | Tipo | Valor Ejemplo | Uso en Aplicación |
|--------|------|---------------|-------------------|
| `DEV_SSH_HOST` | Variable | `dev.example.com` o `192.168.1.100` | **Conexión SSH:** Dirección del servidor donde GitHub Actions hará SSH para deploy. Puede ser dominio o IP. |
| `DEV_SSH_USER` | Variable | `deploy` o `ubuntu` | **Usuario SSH:** Cuenta con permisos para ejecutar comandos en el servidor (docker, git, etc). |
| `DEV_SSH_KEY` | Secret | Ver formato abajo | **Autenticación SSH:** Clave privada que autentica a GitHub Actions en el servidor. Se usa en lugar de contraseña. |
| `DEV_SSH_PORT` | Variable | `22` (default) o `2222` | **Puerto SSH:** Puerto del servicio SSH del servidor. Default es 22, pero puede ser personalizado por seguridad. |
| `DEV_APP_PATH` | Variable | `/var/www/erp-filament` | **Ruta del proyecto:** Path absoluto donde está clonado el repositorio en el servidor. Se usa en todos los comandos `cd`. |

**Formato de DEV_SSH_KEY:**
```
-----BEGIN OPENSSH PRIVATE KEY-----
b3BlbnNzaC1rZXktdjEAAAAABG5vbmUAAAAEbm9uZQAAAAAAAAABAAAAMwAAAAtzc2gtZW
QyNTUxOQAAACBqxxV0Ys5jFZbW9d5Y8OUXMZ4XPGDoE8gGi2KqPyL4lwAAAJhvXS1Ab10t
QAAAAAtzc2gtZWQyNTUxOQAAACBqxxV0Ys5jFZbW9d5Y8OUXMZ4XPGDoE8gGi2KqPyL4lw
AAAEBz+jwXYxGnXqMJoKLQg9M6pNp7V/3a0FkRvH1FPQb5T2rHFXRizmMVltb13ljw5Rcx
nhc8YOgTyAaLYqo/IviXAAAAFGdpdGh1Yi1hY3Rpb25zQGRldi5jb20BAgMEBQ==
-----END OPENSSH PRIVATE KEY-----
```

**⚠️ Importante:** 
- Incluir TODA la clave, desde `-----BEGIN` hasta `-----END`
- No agregar espacios extra ni saltos de línea adicionales
- Esta es la clave generada con `ssh-keygen` en el servidor
- Copiar con: `cat ~/.ssh/github_actions`

---

#### Secrets para Producción

| Secret | Tipo | Valor Ejemplo | Uso en Aplicación |
|--------|------|---------------|-------------------|
| `PROD_SSH_HOST` | Variable | `erp.empresa.com` o `203.0.113.50` | **Conexión SSH:** Dirección del servidor de producción. Preferible usar dominio para facilitar cambios de IP. |
| `PROD_SSH_USER` | Variable | `deploy` o `ubuntu` | **Usuario SSH:** Cuenta con permisos limitados (no root) para deploy. Seguir principio de mínimo privilegio. |
| `PROD_SSH_KEY` | Secret | Ver formato arriba | **Autenticación SSH:** Clave privada única para producción (diferente de dev). Rotar cada 3-6 meses. |
| `PROD_SSH_PORT` | Variable | `22` o `2222` | **Puerto SSH:** Puerto personalizado puede agregar capa de seguridad contra escaneos automatizados. |
| `PROD_APP_PATH` | Variable | `/var/www/erp-filament` | **Ruta del proyecto:** Path donde vive la aplicación. Usado en comandos de deploy: `cd`, `git pull`, etc. |
| `PROD_DOMAIN` | Variable | `erp.empresa.com` | **URL de producción:** Se usa en: health check (`curl https://$DOMAIN/health`), environment URL en GitHub, notificaciones. |

---

#### Otros Secrets Recomendados

##### 🔐 Secrets de Base de Datos

Si necesitas ejecutar comandos de DB desde CI/CD:

| Secret | Valor Ejemplo | Uso |
|--------|---------------|-----|
| `PROD_DB_PASSWORD` | `SuperSecretPass123!` | Password de PostgreSQL para backups automáticos o verificaciones de deploy |
| `PROD_DB_HOST` | `db.internal.com` (si es externo) | Host de base de datos si no está en mismo servidor |
| `PROD_DB_NAME` | `erp_production` | Nombre de la base de datos |

**Ejemplo de uso:**
```yaml
- name: Test database connection
  run: |
    PGPASSWORD=${{ secrets.PROD_DB_PASSWORD }} \
    psql -h ${{ secrets.PROD_DB_HOST }} \
    -U postgres -d ${{ secrets.PROD_DB_NAME }} \
    -c "SELECT 1;"
```

##### 📧 Secrets de Notificaciones

Para enviar alertas cuando el deploy falla/completa:

| Secret | Valor Ejemplo | Uso |
|--------|---------------|-----|
| `SLACK_WEBHOOK_URL` | `https://hooks.slack.com/services/T00/B00/XX` | Enviar notificaciones a canal de Slack del equipo |
| `DISCORD_WEBHOOK_URL` | `https://discord.com/api/webhooks/...` | Notificaciones a Discord |
| `TELEGRAM_BOT_TOKEN` | `110201543:AAHdqTcvCH1vGWJxfSeofSAs0K5PALDsaw` | Bot de Telegram para alertas |
| `TELEGRAM_CHAT_ID` | `-1001234567890` | ID del chat/grupo donde enviar mensajes |

**Ejemplo de uso:**
```yaml
- name: Notify Slack on success
  if: success()
  run: |
    curl -X POST ${{ secrets.SLACK_WEBHOOK_URL }} \
    -H 'Content-Type: application/json' \
    -d '{"text":"✅ Deploy a producción exitoso!"}'
```

##### 🔑 Secrets de APIs Externas

Si la aplicación usa servicios externos:

| Secret | Valor Ejemplo | Uso |
|--------|---------------|-----|
| `AWS_ACCESS_KEY_ID` | `AKIAIOSFODNN7EXAMPLE` | Acceso a S3 para subir assets o backups |
| `AWS_SECRET_ACCESS_KEY` | `wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY` | Clave secreta de AWS |
| `SENTRY_AUTH_TOKEN` | `sntrys_abc123...` | Crear releases en Sentry para tracking de errores |
| `CLOUDFLARE_API_TOKEN` | `abc123def456...` | Limpiar caché de Cloudflare después de deploy |
| `DOCKER_HUB_TOKEN` | `dckr_pat_abc123...` | Subir imágenes Docker a Docker Hub |

**Ejemplo de uso:**
```yaml
- name: Upload assets to S3
  env:
    AWS_ACCESS_KEY_ID: ${{ secrets.AWS_ACCESS_KEY_ID }}
    AWS_SECRET_ACCESS_KEY: ${{ secrets.AWS_SECRET_ACCESS_KEY }}
  run: |
    aws s3 sync ./public/build/ s3://my-bucket/assets/
```

##### 🛡️ Secrets de Seguridad

| Secret | Valor Ejemplo | Uso |
|--------|---------------|-----|
| `SONARQUBE_TOKEN` | `sqp_abc123...` | Análisis de calidad y seguridad de código |
| `SNYK_TOKEN` | `abc-123-def-456` | Escaneo de vulnerabilidades en dependencias |
| `GPG_PRIVATE_KEY` | `-----BEGIN PGP PRIVATE KEY BLOCK-----...` | Firmar releases o commits |

##### 📊 Secrets de Monitoreo

| Secret | Valor Ejemplo | Uso |
|--------|---------------|-----|
| `NEWRELIC_API_KEY` | `NRAK-ABC123...` | Notificar deploy a New Relic APM |
| `DATADOG_API_KEY` | `abc123def456...` | Crear deployment marker en Datadog |

**Ejemplo de uso:**
```yaml
- name: Notify New Relic of deployment
  run: |
    curl -X POST 'https://api.newrelic.com/v2/applications/$APP_ID/deployments.json' \
    -H "X-Api-Key:${{ secrets.NEWRELIC_API_KEY }}" \
    -H 'Content-Type: application/json' \
    -d '{"deployment":{"revision":"${{ github.sha }}"}}'
```

##### 🔐 Secrets de Certificados SSL

Si renuevas certificados vía API:

| Secret | Valor Ejemplo | Uso |
|--------|---------------|-----|
| `CERTBOT_DNS_CLOUDFLARE_TOKEN` | `abc123...` | Renovar certificados Let's Encrypt vía DNS |
| `SSL_CERTIFICATE` | `-----BEGIN CERTIFICATE-----...` | Certificate content si no usas Let's Encrypt |
| `SSL_PRIVATE_KEY` | `-----BEGIN PRIVATE KEY-----...` | Private key para SSL |

---

#### 🎯 ¿Qué Guardar como Secret vs Variable?

**📕 Secrets (Cifrados):**
- ✅ Contraseñas
- ✅ Tokens de API
- ✅ Claves SSH privadas
- ✅ Certificados privados
- ✅ Webhooks URLs (contienen tokens)
- ✅ Cualquier dato que si se filtra compromete seguridad

**📗 Variables (No cifradas):**
- ✅ Nombres de usuario (no sensibles)
- ✅ Dominios/hosts
- ✅ Puertos
- ✅ Paths de aplicación
- ✅ Nombres de bases de datos
- ✅ IDs de aplicaciones (no secretos)
- ✅ Configuraciones públicas

**Regla de oro:** Si te preocuparía que aparezca en Google, es un Secret.

---

#### 🔧 Cómo Obtener/Generar Cada Secret

##### SSH Keys (DEV_SSH_KEY, PROD_SSH_KEY)

```bash
# En el servidor (dev o prod)
ssh-keygen -t ed25519 -C "github-actions@tuempresa.com" -f ~/.ssh/github_actions

# Ver la clave PRIVADA (para GitHub Secret)
cat ~/.ssh/github_actions
# Copiar TODO el contenido, incluidos headers

# Ver la clave PÚBLICA (para authorized_keys del servidor)
cat ~/.ssh/github_actions.pub

# Agregar clave pública al servidor
cat ~/.ssh/github_actions.pub >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys

# Verificar permisos
ls -la ~/.ssh/
# github_actions debe ser 600 (-rw-------)
# authorized_keys debe ser 600 (-rw-------)
```

##### IP/Host del Servidor (DEV_SSH_HOST, PROD_SSH_HOST)

```bash
# Opción 1: Ver IP pública del servidor
curl ifconfig.me

# Opción 2: Ver IP desde el servidor
hostname -I

# Opción 3: Usar dominio (recomendado)
# Verifica que resuelve correctamente:
nslookup erp.empresa.com
```

##### Usuario SSH (DEV_SSH_USER, PROD_SSH_USER)

```bash
# Ver usuario actual en el servidor
whoami

# Crear usuario específico para deploy (recomendado)
sudo adduser deploy
sudo usermod -aG docker deploy  # Dar acceso a Docker
sudo usermod -aG sudo deploy    # Si necesita sudo

# Configurar SSH para el usuario
sudo su - deploy
mkdir -p ~/.ssh
chmod 700 ~/.ssh
```

##### Path de Aplicación (DEV_APP_PATH, PROD_APP_PATH)

```bash
# En el servidor, ver donde está clonado el proyecto
pwd
# Ejemplo: /var/www/erp-filament

# O buscar el proyecto
find /var /home -name "Laravel_app" 2>/dev/null
```

##### Dominio (PROD_DOMAIN)

```bash
# Verificar configuración de Nginx/Apache
cat /etc/nginx/sites-enabled/default | grep server_name

# Verificar que el dominio apunta al servidor
dig erp.empresa.com +short
# Debe mostrar la IP del servidor
```

##### Tokens de APIs Externas

- **Slack:** https://api.slack.com/messaging/webhooks
- **AWS:** https://console.aws.amazon.com/iam/ → Users → Security credentials
- **Sentry:** https://sentry.io/ → Settings → Auth Tokens
- **Cloudflare:** https://dash.cloudflare.com/profile/api-tokens

---

#### 🛡️ Mejores Prácticas de Seguridad para Secrets

##### 1. Rotación de Secrets

```bash
# Cada 3-6 meses, rotar claves SSH

# En el servidor:
# 1. Generar nueva clave
ssh-keygen -t ed25519 -C "github-actions-new" -f ~/.ssh/github_actions_new

# 2. Agregar nueva clave a authorized_keys
cat ~/.ssh/github_actions_new.pub >> ~/.ssh/authorized_keys

# 3. Actualizar secret en GitHub con contenido de github_actions_new

# 4. Probar deploy con nueva clave

# 5. Solo entonces, remover clave vieja de authorized_keys
nano ~/.ssh/authorized_keys  # Borrar línea de clave vieja

# 6. Borrar archivo de clave vieja
rm ~/.ssh/github_actions
```

##### 2. Separar Secrets por Ambiente

**❌ No hacer:**
```yaml
SSH_KEY = (misma clave para dev y prod)
DB_PASSWORD = (mismo password)
```

**✅ Correcto:**
```yaml
DEV_SSH_KEY = (clave única para dev)
PROD_SSH_KEY = (clave única para prod)
DEV_DB_PASSWORD = (password de dev)
PROD_DB_PASSWORD = (password de prod diferente)
```

**Beneficio:** Si se compromete dev, producción sigue segura.

##### 3. Principio de Mínimo Privilegio

```bash
# Crear usuario deploy con permisos limitados
sudo adduser deploy

# Solo darle acceso a:
# - Docker (sin sudo)
sudo usermod -aG docker deploy

# - Directorio del proyecto
sudo chown -R deploy:deploy /var/www/erp-filament

# NO dar:
# ❌ Acceso root
# ❌ Sudo sin restricciones
# ❌ Acceso a otros proyectos
```

##### 4. Auditar Uso de Secrets

En GitHub: **Settings → Secrets → Actions secrets**

Ver para cada secret:
- Cuándo fue creado
- Última actualización
- Workflows que lo usan

**Eliminar secrets no usados:**
```bash
# Antes de borrar un secret, verificar:
# 1. Buscar en todos los workflows
grep -r "SECRET_NAME" .github/workflows/

# 2. Si no aparece, es seguro borrarlo
```

##### 5. Monitoring de Accesos SSH

```bash
# En el servidor, monitorear logins SSH
sudo journalctl -u ssh -f

# Ver últimos logins
last -a | grep deploy

# Alertar si hay login desde IP desconocida
# Configurar fail2ban para proteger SSH
sudo apt install fail2ban
```

##### 6. Secrets en Logs

GitHub automáticamente oculta secrets en logs, pero verifica:

```yaml
# ❌ NUNCA hacer esto:
- name: Debug
  run: echo "Password is ${{ secrets.DB_PASSWORD }}"
  # Aunque GitHub lo oculta, es mala práctica

# ✅ Si necesitas debug:
- name: Check secret exists
  run: |
    if [ -z "${{ secrets.DB_PASSWORD }}" ]; then
      echo "Secret is empty!"
    else
      echo "Secret is set ✅"
    fi
```

##### 7. Backup de Secrets

**Importante:** Guarda secrets en gestor de contraseñas del equipo.

Herramientas recomendadas:
- **1Password** (Teams)
- **Bitwarden** (Open source)
- **LastPass** (Teams)
- **HashiCorp Vault** (Empresarial)

**No guardar:**
- ❌ En archivos .txt en tu computadora
- ❌ En Notion/Google Docs sin cifrar
- ❌ En comentarios de código
- ❌ En Slack/Discord

---

#### 📋 Checklist de Secrets

Antes de hacer el primer deploy, verifica:

**Para Desarrollo:**
- [ ] `DEV_SSH_HOST` configurado y servidor accesible
- [ ] `DEV_SSH_USER` tiene permisos de docker y git
- [ ] `DEV_SSH_KEY` es la clave privada correcta
- [ ] La clave pública está en `~/.ssh/authorized_keys` del servidor
- [ ] `DEV_SSH_PORT` correcto (probar con `ssh -p PORT usuario@host`)
- [ ] `DEV_APP_PATH` existe y contiene el proyecto
- [ ] Testear conexión SSH desde tu máquina local

**Para Producción:**
- [ ] `PROD_SSH_HOST` configurado y servidor accesible
- [ ] `PROD_SSH_USER` tiene permisos mínimos necesarios
- [ ] `PROD_SSH_KEY` diferente a la de dev
- [ ] Firewall permite SSH solo desde IPs de GitHub Actions
- [ ] `PROD_DOMAIN` resuelve correctamente a IP del servidor
- [ ] Certificados SSL configurados si es HTTPS
- [ ] Testear conexión SSH desde tu máquina local
- [ ] Backup de todos los secrets en gestor de contraseñas

**Verificar que funciona:**
```bash
# Desde tu máquina local, probar con la clave de GitHub Actions:
ssh -i ~/.ssh/github_actions usuario@servidor "cd /var/www/erp-filament && git status"

# Si funciona, el secret está correcto ✅
```

---

### Paso 4: Verificar Workflows

Los workflows están en `.github/workflows/`:

```
.github/
└── workflows/
    ├── develop.yml      → Deploy automático a servidor dev
    ├── production.yml   → Deploy automático a servidor prod
    └── main.yml         → Tests básicos (legacy)
```

---

## 📖 Guía de Desarrollo Diario

### Escenario 1: Nueva Funcionalidad

```bash
# 1. Actualizar tu rama local
git checkout develop
git pull origin develop

# 2. Crear rama de feature
git checkout -b feature/sistema-de-reportes

# 3. Desarrollar y testear localmente
# ... codificar ...
php artisan test

# 4. Commit con mensaje descriptivo
git add .
git commit -m "feat: agregar módulo de reportes financieros"

# 5. Push a GitHub
git push origin feature/sistema-de-reportes

# 6. Abrir Pull Request en GitHub
# Ir a GitHub → Pull requests → New pull request
# Base: develop ← Compare: feature/sistema-de-reportes

# 7. Esperar tests automáticos ✅
# GitHub Actions ejecutará tests automáticamente

# 8. Code review y merge
# Después de aprobación, hacer merge a develop

# 9. Deploy automático a DEV
# GitHub Actions desplegará automáticamente a dev.example.com
# ⚡ AUTOMÁTICO: El merge dispara el workflow develop.yml
# Puedes ver el progreso en: GitHub → Actions tab
# Espera ~10 minutos hasta que termine

# 10. Probar en servidor dev
# Verificar que todo funciona en http://dev.example.com
```

### Escenario 2: Hotfix en Producción

```bash
# 1. Crear rama desde main
git checkout main
git pull origin main
git checkout -b hotfix/corregir-calculo-impuestos

# 2. Aplicar fix y testear
# ... corregir código ...
php artisan test

# 3. Commit y push
git add .
git commit -m "fix: corregir cálculo de IVA en facturas"
git push origin hotfix/corregir-calculo-impuestos

# 4. Pull Request directo a main (URGENTE)
# Base: main ← Compare: hotfix/corregir-calculo-impuestos

# 5. Aprobar y merge rápido
# Después de tests y aprobación

# 6. Deploy automático a PRODUCCIÓN
# GitHub Actions desplegará a producción

# 7. Mergear hotfix también a develop
git checkout develop
git merge hotfix/corregir-calculo-impuestos
git push origin develop
```

### Escenario 3: Release a Producción

```bash
# 1. Verificar que develop está estable
# Probar todo en dev.example.com

# 2. Crear Pull Request de release
# En GitHub: develop → main
# Título: "Release v1.2.0 - Nuevas funcionalidades"

# 3. Revisión exhaustiva
# - Product Owner aprueba
# - Tech Lead revisa
# - Tests pasan ✅

# 4. Merge a main
# Botón "Merge pull request"

# 5. Deploy automático a producción
# production.yml se ejecuta automáticamente
# ⚡ AUTOMÁTICO: El merge dispara el workflow production.yml
# NO necesitas hacer nada más, GitHub Actions se encarga de todo

# 6. Monitorear deploy
# Ver logs en Actions tab en GitHub
# Espera ~20 minutos hasta que termine
# Verificar aplicación en erp.empresa.com

# 7. Crear tag de versión
git checkout main
git pull origin main
git tag -a v1.2.0 -m "Release 1.2.0: Sistema de reportes"
git push origin v1.2.0
```

---

## 🔍 Workflows Explicados

### Archivo: `.github/workflows/develop.yml`

**Propósito:** Deploy automático al servidor de desarrollo.

**Disparo:**
```yaml
on:
  push:
    branches: [ "develop" ]
```

**Jobs:**

#### Job 1: `test`
```yaml
- Checkout código
- Setup Node.js y PHP
- Crear .env de testing
- Build contenedores Docker
- Iniciar servicios
- Esperar a que DB esté lista
- Instalar dependencias PHP
- Ejecutar migraciones
- Ejecutar tests (PHPUnit)
- Análisis de código (PHPStan)
- Apagar contenedores
```

**Duración estimada:** 5-8 minutos

#### Job 2: `deploy-dev`
```yaml
- Conectar via SSH al servidor dev
- Pull código desde rama develop
- Reconstruir contenedor PHP
- Reiniciar servicios Docker
- Instalar dependencias
- Ejecutar migraciones
- Cachear configuraciones
- Reiniciar PHP-FPM
```

**Duración estimada:** 2-3 minutos

**Total:** ~10 minutos desde push hasta deploy completo.

---

### Archivo: `.github/workflows/production.yml`

**Propósito:** Deploy automático al servidor de producción con safety checks.

**Disparo:**
```yaml
on:
  push:
    branches: [ "main" ]
```

**Jobs:**

#### Job 1: `test`
```yaml
- Checkout código
- Crear .env de producción
- Build contenedores con --no-cache
- Ejecutar suite completa de tests
- Tests con configuración de producción
- Validar que no hay dependencias de dev
```

**Duración:** 8-10 minutos (más exhaustivo)

#### Job 2: `build-assets`
```yaml
- Setup Node.js con caché
- Compilar assets de Laravel (Vite)
- Compilar assets de Vue.js
- Subir artifacts a GitHub
```

**Duración:** 3-5 minutos

#### Job 3: `deploy-prod`
```yaml
- Descargar artifacts compilados
- Activar modo mantenimiento (php artisan down)
- Pull código desde main
- Detener servicios Docker
- Rebuild contenedor PHP con --no-cache
- Iniciar servicios
- Instalar dependencias de producción
- Ejecutar migraciones con --force
- Copiar assets compilados
- Cachear todo (config, routes, views, events)
- Generar permisos Shield
- Reiniciar PHP-FPM
- Desactivar modo mantenimiento (php artisan up)
```

**Duración:** 5-8 minutos

**Total:** ~20 minutos desde merge hasta producción.

---

### Archivo: `.github/workflows/main.yml`

**Propósito:** Tests básicos para cualquier branch (workflow legacy).

**Uso:** Se ejecuta en cualquier push/PR a main para validar código.

---

## 🌿 Gestión de Ramas

### Ramas Principales

#### `main`
- **Propósito:** Código estable en producción
- **Deploy:** Automático a servidor de producción
- **Protección:** Branch protection activado
- **Reglas:**
  - Requiere PR aprobado
  - Tests deben pasar
  - No se puede hacer push directo

#### `develop`
- **Propósito:** Integración de features en desarrollo
- **Deploy:** Automático a servidor de desarrollo
- **Protección:** Branch protection recomendado
- **Reglas:**
  - Requiere PR desde feature/*
  - Tests deben pasar

### Ramas de Trabajo

#### `feature/*`
**Ejemplo:** `feature/modulo-facturacion`
- Para nuevas funcionalidades
- Se crean desde `develop`
- Se mergan a `develop` via PR

#### `bugfix/*`
**Ejemplo:** `bugfix/corregir-validacion-email`
- Para bugs en desarrollo
- Se crean desde `develop`
- Se mergan a `develop` via PR

#### `hotfix/*`
**Ejemplo:** `hotfix/seguridad-autenticacion`
- Para bugs críticos en producción
- Se crean desde `main`
- Se mergan a `main` y luego a `develop`

### Comandos Útiles

```bash
# Ver todas las ramas
git branch -a

# Crear y cambiar a nueva rama
git checkout -b feature/mi-feature

# Actualizar rama con últimos cambios de develop
git checkout feature/mi-feature
git rebase develop

# Borrar rama local después de merge
git branch -d feature/mi-feature

# Borrar rama remota
git push origin --delete feature/mi-feature

# Ver diferencias entre ramas
git diff develop..main
```

---

## 🔐 Secretos y Variables

> **💡 Tip:** Para información detallada sobre cada secret, formatos, cómo obtenerlos y mejores prácticas de seguridad, consulta la sección [Paso 3: Configurar GitHub Secrets](#paso-3-configurar-github-secrets) en Configuración Inicial.

### Resumen de Secrets Requeridos

#### Para Desarrollo
- `DEV_SSH_HOST` - Host/IP del servidor de desarrollo
- `DEV_SSH_USER` - Usuario SSH del servidor
- `DEV_SSH_KEY` - Clave SSH privada (formato ed25519)
- `DEV_SSH_PORT` - Puerto SSH (default: 22)
- `DEV_APP_PATH` - Path del proyecto en el servidor

#### Para Producción
- `PROD_SSH_HOST` - Host/IP del servidor de producción
- `PROD_SSH_USER` - Usuario SSH del servidor
- `PROD_SSH_KEY` - Clave SSH privada (diferente a dev)
- `PROD_SSH_PORT` - Puerto SSH
- `PROD_APP_PATH` - Path del proyecto
- `PROD_DOMAIN` - Dominio de producción (para health check)

- `PROD_DOMAIN` - Dominio de producción (para health check)

#### Secrets Opcionales (Extender Funcionalidad)
- Notificaciones: `SLACK_WEBHOOK_URL`, `TELEGRAM_BOT_TOKEN`
- APIs: `AWS_ACCESS_KEY_ID`, `SENTRY_AUTH_TOKEN`, `CLOUDFLARE_API_TOKEN`
- Base de datos: `PROD_DB_PASSWORD` (si necesitas acceso directo desde CI)

Ver lista completa y casos de uso en [Otros Secrets Recomendados](#otros-secrets-recomendados).

### Cómo Agregar Secretos (Resumen Rápido)

1. Ve a tu repositorio en GitHub
2. **Settings** → **Secrets and variables** → **Actions**
3. **New repository secret**
4. Nombre: `PROD_SSH_KEY` (exactamente como aparece en el workflow)
4. Nombre: `PROD_SSH_KEY` (exactamente como aparece en el workflow)
5. Value: Pega el contenido completo (ver [guía detallada](#🔧-cómo-obtenergenerar-cada-secret))
6. **Add secret**

**Ejemplo de valores (enmascarados):**
```
PROD_SSH_HOST = "203.0.113.50" o "erp.empresa.com"
PROD_SSH_USER = "deploy"
PROD_SSH_KEY = "-----BEGIN OPENSSH PRIVATE KEY-----\nb3BlbnNzaC1rZ...\n-----END OPENSSH PRIVATE KEY-----"
PROD_APP_PATH = "/var/www/erp-filament"
PROD_DOMAIN = "https://erp.empresa.com"
```

### Usar Secretos en Workflows

```yaml
steps:
  - name: Deploy
    uses: appleboy/ssh-action@v1.1.0
    with:
      host: ${{ secrets.PROD_SSH_HOST }}      # Acceder al secret
      username: ${{ secrets.PROD_SSH_USER }}
      key: ${{ secrets.PROD_SSH_KEY }}
      script: |
        echo "Deploy script aquí"
```

### Seguridad de Secrets

**Características de GitHub Secrets:**
- ✅ Cifrados con AES-256-GCM en reposo
- ✅ Nunca aparecen en logs (GitHub los enmascara automáticamente)
- ✅ Solo accesibles durante la ejecución del workflow
- ✅ No se pueden leer después de crearlos (solo actualizar)

**Mejores prácticas:**
- ⚠️ No compartir secrets en branches públicos o forks
- ⚠️ Rotar periódicamente (cada 3-6 meses)
- ⚠️ Usar secrets diferentes para dev y prod
- ⚠️ Aplicar principio de mínimo privilegio
- ⚠️ Guardar backup en gestor de contraseñas del equipo

> **📖 Guía completa:** Ver [Mejores Prácticas de Seguridad para Secrets](#🛡️-mejores-prácticas-de-seguridad-para-secrets) para rotación, monitoreo, y auditoría.

### Tabla de Referencia Rápida

| Secret | Comando para Obtener | Ejemplo de Valor |
|--------|---------------------|------------------|
| `*_SSH_HOST` | `curl ifconfig.me` en servidor | `203.0.113.50` |
| `*_SSH_USER` | `whoami` en servidor | `deploy` |
| `*_SSH_KEY` | `cat ~/.ssh/github_actions` | `-----BEGIN OPENSSH...` |
| `*_SSH_PORT` | Revisar config SSH | `22` |
| `*_APP_PATH` | `pwd` en directorio del proyecto | `/var/www/erp-filament` |
| `PROD_DOMAIN` | Configuración de nginx | `https://erp.empresa.com` |

**Verificar secrets funcionan:**
```bash
# Testear conexión SSH con la clave de GitHub Actions
ssh -i ~/.ssh/github_actions deploy@erp.empresa.com "cd /var/www/erp-filament && git status"

# Si funciona → Los secrets están correctos ✅
# Si falla → Revisar host, usuario, o permisos de la clave
```

---

## 📊 Monitoreo y Logs

### Ver Ejecución de Workflows

1. Ve a tu repositorio en GitHub
2. Click en tab **Actions**
3. Verás lista de todos los workflows ejecutados:
   ```
   ✅ Deploy to Production #42 - main
   ⏳ Deploy to Development #83 - develop (en progreso)
   ❌ Deploy to Development #82 - develop (falló)
   ```

### Ver Logs Detallados

1. Click en el workflow que quieres ver
2. Click en el job (ej: "test", "deploy-prod")
3. Expandir steps para ver logs de cada comando

### Logs en Tiempo Real

Mientras el workflow está corriendo, los logs se actualizan en tiempo real.

### Filtrar Workflows

```
Filtrar por:
- Branch: develop, main
- Status: success, failure, in_progress
- Event: push, pull_request
- Actor: @usuario que disparó el workflow
```

### Notificaciones

GitHub envía notificaciones cuando:
- ✅ Workflow completa exitosamente
- ❌ Workflow falla
- ⏸️ Workflow requiere aprobación manual

**Configurar notificaciones:**
- **Settings** → **Notifications** → **Actions**
- Activa: Email, Web, GitHub Mobile

### Verificar Deploy en Servidor

```bash
# Conectar al servidor
ssh usuario@erp.empresa.com

# Ver logs del deploy
cd /var/www/erp-filament

# Ver último commit desplegado
git log -1

# Ver logs de Docker
docker compose logs --tail=50 php
docker compose logs --tail=50 nginx

# Ver logs de Laravel
docker compose exec php tail -n 100 /var/www/html/Laravel_app/storage/logs/laravel.log
```

---

## 🐛 Troubleshooting

### Problema 1: Tests Fallan en GitHub Actions pero Pasan Local

**Causa:** Diferencias de ambiente entre local y CI.

**Solución:**
```bash
# Ejecutar tests con configuración de CI
cp Laravel_app/.env.testing Laravel_app/.env
docker compose -f docker-compose.base.yml -f docker-compose.dev.yml --profile dev up -d
docker compose exec -w /var/www/html/Laravel_app php php artisan test
```

Revisa:
- Versiones de PHP/Node
- Variables de entorno en .env.testing
- Dependencias en composer.json

---

### Problema 2: SSH Connection Refused

**Error en logs:**
```
Error: SSH connection refused
```

**Solución:**

1. Verificar que el servidor SSH está corriendo:
```bash
# En el servidor
sudo systemctl status ssh
```

2. Verificar firewall:
```bash
sudo ufw status
sudo ufw allow 22/tcp
```

3. Verificar que la clave SSH es correcta:
```bash
# En tu máquina local, testear conexión
ssh -i github_actions usuario@servidor
```

4. Verificar secrets en GitHub:
- `PROD_SSH_HOST` tiene el dominio correcto
- `PROD_SSH_KEY` tiene la clave PRIVADA completa
- La clave pública está en `~/.ssh/authorized_keys` del servidor

---

### Problema 3: Deploy se Ejecuta pero Aplicación no Actualiza

**Error:** El workflow pasa ✅ pero el código no cambia en el servidor.

**Causas comunes:**

#### A. Git no está actualizado
```bash
# En el servidor
cd /var/www/erp-filament
git status
git log -1   # Ver último commit

# Forzar pull
git fetch origin
git reset --hard origin/main  # o origin/develop
```

#### B. Docker no rebuildeó
```bash
# Rebuild forzado
docker compose -f docker-compose.base.yml -f docker-compose.prod.yml build --no-cache php
docker compose restart php
```

#### C. Caché de Laravel
```bash
# Limpiar cachés
docker compose exec -w /var/www/html/Laravel_app php php artisan cache:clear
docker compose exec -w /var/www/html/Laravel_app php php artisan config:clear
docker compose exec -w /var/www/html/Laravel_app php php artisan route:clear
docker compose exec -w /var/www/html/Laravel_app php php artisan view:clear

# Volver a cachear
docker compose exec -w /var/www/html/Laravel_app php php artisan config:cache
docker compose exec -w /var/www/html/Laravel_app php php artisan route:cache
docker compose exec -w /var/www/html/Laravel_app php php artisan view:cache
```

---

### Problema 4: Assets no se Cargan Después de Deploy

**Error:** CSS/JS muestran versiones antiguas o 404.

**Solución:**

1. Verificar que artifacts se subieron:
```bash
# En el servidor
ls -lh /var/www/erp-filament/Laravel_app/public/build/
ls -lh /var/www/erp-filament/Vue_app/dist/
```

2. Si faltan, recompilar manualmente:
```bash
cd /var/www/erp-filament

# Laravel assets
cd Laravel_app
npm ci
npm run build
cd ..

# Vue assets
cd Vue_app
npm ci
npm run build
cd ..

# Reiniciar Nginx
docker compose restart nginx
```

3. Limpiar caché del navegador: `Ctrl + Shift + R`

---

### Problema 5: Migrations Fail en Deploy

**Error:**
```
SQLSTATE[42P01]: Undefined table
```

**Causas:**

#### A. Tabla ya existe
```bash
# En el servidor
docker compose exec db psql -U postgres -d erp_production -c "\dt"

# Si la tabla existe, marcar migración como ejecutada
docker compose exec -w /var/www/html/Laravel_app php php artisan migrate:status
```

#### B. Base de datos no está sincronizada
```bash
# Opción 1: Ejecutar migraciones faltantes
docker compose exec -w /var/www/html/Laravel_app php php artisan migrate --force

# Opción 2: Fresh (PELIGRO: borra toda la data)
docker compose exec -w /var/www/html/Laravel_app php php artisan migrate:fresh --force --seed
```

---

### Problema 6: Workflow Queda en Cola (Pending)

**Síntoma:** Workflow no inicia, queda con ícono amarillo ⏳.

**Causas:**

#### A. Límite de concurrencia
GitHub Actions tiene límites:
- Free: 20 jobs concurrentes
- Pro: 40 jobs concurrentes

**Solución:** Esperar a que otros workflows terminen.

#### B. Self-hosted runner offline
Si usas runners propios:

```bash
# Verificar estado del runner
./run.sh status

# Reiniciar runner
./run.sh restart
```

---

### Problema 7: Out of Memory al Compilar Assets

**Error:**
```
FATAL ERROR: Ineffective mark-compacts near heap limit
```

**Solución:**

Aumentar memoria para Node.js en workflow:

```yaml
# En develop.yml o production.yml
- name: Build Laravel assets
  run: |
    cd Laravel_app
    export NODE_OPTIONS="--max-old-space-size=4096"
    npm ci
    npm run build
```

---

### Problema 8: Permission Denied en Deploy

**Error:**
```
Permission denied (publickey)
```

**Solución:**

1. Verificar formato de la clave SSH en GitHub Secrets:
```
-----BEGIN OPENSSH PRIVATE KEY-----
b3BlbnNzaC1rZXktdjEAAAAABG5vbmUAAAAEbm9uZQAAAAAAAAABAAAAMwAAAAtz
...
-----END OPENSSH PRIVATE KEY-----
```

2. Asegurarse que incluye header y footer completos

3. Verificar que no hay espacios extra al copiar/pegar

4. Regenerar claves si es necesario:
```bash
ssh-keygen -t ed25519 -C "github-actions" -f ~/.ssh/github_new
```

---

## ✨ Mejores Prácticas

### 1. Commits Semánticos

Usa prefijos en mensajes de commit:

```bash
feat: agregar nueva funcionalidad
fix: corregir bug
docs: actualizar documentación
style: cambios de formato (sin afectar código)
refactor: refactorizar código
test: agregar tests
chore: tareas de mantenimiento
perf: mejoras de rendimiento
```

**Ejemplo:**
```bash
git commit -m "feat(facturacion): agregar cálculo automático de impuestos"
git commit -m "fix(auth): corregir validación de email en registro"
```

### 2. Pull Requests Descriptivos

Template de PR:

```markdown
## 📝 Descripción
Breve descripción de los cambios realizados.

## 🎯 Tipo de Cambio
- [ ] Nueva funcionalidad (feature)
- [ ] Corrección de bug (bugfix)
- [ ] Cambio que rompe compatibilidad (breaking change)
- [ ] Documentación

## 🧪 Tests
- [ ] Tests unitarios agregados/actualizados
- [ ] Tests de integración actualizados
- [ ] Probado manualmente en local

## 📸 Screenshots
(Si aplica)

## ✅ Checklist
- [ ] El código sigue los estándares del proyecto
- [ ] He revisado mi propio código
- [ ] He comentado código complejo
- [ ] He actualizado la documentación
- [ ] Mis cambios no generan warnings
- [ ] Tests pasan localmente
```

### 3. Protección de Ramas

Configurar en GitHub: **Settings → Branches → Add rule**

**Para `main`:**
```
✅ Require pull request before merging
✅ Require approvals: 2
✅ Require status checks to pass: test, build-assets
✅ Require branches to be up to date
✅ Require conversation resolution before merging
✅ Do not allow bypassing the above settings
```

**Para `develop`:**
```
✅ Require pull request before merging
✅ Require approvals: 1
✅ Require status checks to pass: test
```

### 4. Versionado Semántico

Usa tags para release:

```bash
# Major release (cambios incompatibles)
git tag -a v2.0.0 -m "Release 2.0.0: Nueva arquitectura"

# Minor release (nuevas features)
git tag -a v1.5.0 -m "Release 1.5.0: Módulo de reportes"

# Patch release (bugfixes)
git tag -a v1.4.1 -m "Release 1.4.1: Hotfix cálculo IVA"

# Push tags
git push origin --tags
```

### 5. Rollback Rápido

Si un deploy a producción falla:

#### Opción A: Revert en Git
```bash
# Localmente
git checkout main
git revert HEAD  # Revierte último commit
git push origin main
# Esto dispara deploy automático con código anterior
```

#### Opción B: Rollback Manual en Servidor
```bash
# En el servidor prod
cd /var/www/erp-filament

# Ver commits recientes
git log --oneline -5

# Volver a commit anterior
git reset --hard abc1234  # Hash del commit bueno

# Reiniciar servicios
docker compose restart php nginx
```

### 6. Monitoreo Post-Deploy

Después de cada deploy a producción:

```bash
# 1. Verificar que servicios están corriendo
docker compose ps

# 2. Verificar logs por errores
docker compose logs --tail=100 | grep -i error

# 3. Verificar health endpoint
curl https://erp.empresa.com/health

# 4. Verificar Laravel logs
docker compose exec php tail -50 /var/www/html/Laravel_app/storage/logs/laravel.log

# 5. Monitorear métricas
docker stats --no-stream
```

### 7. Backups Antes de Deploys Importantes

```bash
# Antes de merge a main
# En servidor de producción
cd /var/www/erp-filament

# Backup de código
git rev-parse HEAD > /tmp/last_deploy.txt

# Backup de base de datos
docker compose exec -T db pg_dump -U postgres -d erp_production | gzip > /backups/pre_deploy_$(date +%Y%m%d_%H%M%S).sql.gz
```

### 8. Variables de Entorno

No hardcodear valores sensibles:

**❌ Incorrecto:**
```php
$apiKey = "sk_live_12345abcde";
```

**✅ Correcto:**
```php
$apiKey = env('PAYMENT_API_KEY');
```

Y en `.env`:
```
PAYMENT_API_KEY=sk_live_12345abcde
```

### 9. Testing en CI

Asegurar que tests cubren casos críticos:

```php
// tests/Feature/PaymentTest.php
public function test_payment_processes_correctly()
{
    // Arrange
    $order = Order::factory()->create();
    
    // Act
    $response = $this->post('/payment', [
        'order_id' => $order->id,
        'amount' => 100.00
    ]);
    
    // Assert
    $response->assertStatus(200);
    $this->assertDatabaseHas('payments', [
        'order_id' => $order->id,
        'status' => 'completed'
    ]);
}
```

### 10. Documentación de Cambios

Mantener CHANGELOG.md actualizado:

```markdown
# Changelog

## [1.5.0] - 2026-02-21

### Added
- Módulo de reportes financieros
- Exportación a PDF de facturas
- Dashboard de métricas en tiempo real

### Changed
- Mejorado rendimiento de consultas de productos
- Actualizado diseño del módulo de usuarios

### Fixed
- Corregido cálculo de IVA en facturas internacionales
- Solucionado bug de sesiones en multi-tab

### Security
- Actualizado Laravel a 11.x
- Parcheado vulnerabilidad en autenticación
```

---

## 📚 Recursos Adicionales

### Documentación Oficial

- **GitHub Actions:** https://docs.github.com/en/actions
- **Laravel Deployment:** https://laravel.com/docs/deployment
- **Docker Compose:** https://docs.docker.com/compose/

### Workflows de Referencia

Ver workflows actuales en `.github/workflows/`:
- [develop.yml](.github/workflows/develop.yml) - Deploy a desarrollo
- [production.yml](.github/workflows/production.yml) - Deploy a producción
- [main.yml](.github/workflows/main.yml) - Tests básicos

### Comandos de Referencia Rápida

```bash
# Ver estado de workflow desde CLI
gh workflow view

# Ejecutar workflow manualmente
gh workflow run production.yml

# Ver logs de último workflow
gh run view --log

# Re-ejecutar workflow fallido
gh run rerun <run-id>
```

---

## 🎓 Resumen Ejecutivo

### Para Desarrolladores Junior

**Tu flujo básico:**
1. `git checkout develop && git pull`
2. `git checkout -b feature/mi-feature`
3. Codificar + testear localmente
4. `git push origin feature/mi-feature`
5. Abrir PR a `develop` en GitHub
6. Esperar tests y aprobación
7. Merge → Deploy automático a dev

**NO tocar:** Ramas `main`, configuración de CI/CD, secrets.

### Para Tech Leads

**Tus responsabilidades:**
- Aprobar PRs a `develop` y `main`
- Monitorear workflows en GitHub Actions
- Gestionar secrets y variables
- Configurar branch protection
- Resolver conflictos de deployment
- Rollback en caso de problemas

### Para DevOps

**Configuración a mantener:**
- Servidores SSH con claves correctas
- GitHub Secrets actualizados
- Workflow files optimizados
- Monitoreo de runners
- Backups automáticos pre-deploy

---

## 📞 Soporte

**¿Workflow falló?**
1. Revisa logs en GitHub Actions
2. Consulta sección [Troubleshooting](#-troubleshooting)
3. Verifica estado de servidores

**¿Deploy no funciona?**
1. Verifica conectividad SSH: `ssh usuario@servidor`
2. Revisa logs de servidor: `docker compose logs`
3. Compara `.env` del servidor con lo esperado

**¿Tests pasan local pero fallan en CI?**
1. Ejecuta tests con configuración de CI (ver arriba)
2. Revisa diferencias de ambiente
3. Valida dependencias de `composer.json` y `package.json`

---

## ✅ Checklist de Configuración Completa

### Inicial (Una Sola Vez)

- [ ] Servidores dev y prod configurados
- [ ] Docker corriendo en ambos servidores
- [ ] SSH configurado con claves
- [ ] GitHub Secrets configurados
- [ ] Branch protection activado
- [ ] Workflows funcionando
- [ ] Tests ejecutándose correctamente

### Por Deploy

- [ ] Tests pasan ✅
- [ ] Code review completado
- [ ] PR aprobado
- [ ] Documentación actualizada
- [ ] Changelog actualizado
- [ ] Backup pre-deploy realizado
- [ ] Monitoreo post-deploy OK

---

**🚀 ¡Listo! Ahora tienes un sistema CI/CD completamente automatizado.**

Cualquier push a `develop` o `main` desplegará automáticamente a sus respectivos servidores después de pasar todas las validaciones.

**Happy Deploying! 🎉**
