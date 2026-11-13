# ERP Filament - Guía de Deployment

Sistema ERP basado en Laravel + Filament con frontend Vue.js, completamente dockerizado.

## Roles de usuarios del panel

Cada usuario creado desde el panel tiene un rol. Las asignaciones permitidas son:
`super_admin` puede asignar `super_admin`, `admin`, `manager`, `sales` y
`warehouse`; `admin` puede asignar `manager`, `sales` y `warehouse`; y
`manager` puede asignar `sales` y `warehouse`. Los demás roles no pueden asignar
roles. La edición de usuarios y la gestión de roles también requieren sus permisos
habituales; solo `super_admin` puede modificar roles y sus permisos en Shield.

Un administrador puede cambiar su propio rol a uno permitido por la matriz,
incluso si es el último `super_admin`. No puede modificar a otro usuario con un
rol fuera de su ámbito. Los roles deben existir con el guard `web`.

La migración `2026_11_06_000000_limit_users_to_one_role` impide asignar más de
un rol a un `User` en PostgreSQL y SQLite. Si ya hay usuarios con varios roles,
deben regularizarse antes de migrar; la migración no elimina datos. El índice
permite usuarios sin rol, pero el formulario de creación exige uno. Ejecutar
`php artisan migrate` después de desplegar; no es necesario hacer `migrate:fresh`.

## Imágenes de productos

Desarrollo y producción usan el mismo esquema, en VPS independientes. En cada
servidor, antes de iniciar o recrear los contenedores:

```bash
sudo install -d -o 1000 -g 1000 -m 755 ./ERP_storage/public/products
```

Los archivos de Compose montan `./ERP_storage/public` (relativo al proyecto) en
`Laravel_app/storage/app/public`: PHP escribe como UID/GID `1000:1000` y Nginx
lo monta en solo lectura. Comprueba también que Nginx pueda atravesar y leer los
directorios del VPS. El montaje requiere que el directorio exista: no lo crea
automáticamente. Al activarlo, el contenido anterior de `storage/app/public`
queda oculto; antes de recrear los contenedores, respalda y copia al directorio
externo cualquier otro archivo público que deba conservarse.

Después de configurar los montajes, selecciona el archivo Compose del entorno:

```bash
# Usar docker-compose.dev.yml en desarrollo o docker-compose.prod.yml en producción.
docker compose -f docker-compose.dev.yml up -d
# Crear el enlace solo si public/storage todavía no existe.
docker compose -f docker-compose.dev.yml exec -w /var/www/html/Laravel_app php php artisan storage:link
docker compose -f docker-compose.dev.yml exec -w /var/www/html/Laravel_app php php artisan migrate --force
```

Verifica que `public/storage` no exista ya como directorio y que el enlace
apunte a `storage/app/public`. Configura `APP_URL` en Laravel con la URL HTTPS
del administrador accesible por los navegadores y los canales externos. Si se
usa configuración cacheada, actualízala después de modificar el `.env`.
El archivo de configuración PHP de ambos entornos permite las cargas del panel:
como máximo 10 imágenes JPEG, PNG o WebP de 5 MiB cada una.

Las imágenes nuevas se convierten a WebP (calidad 85, máximo 12 megapíxeles) y
se guardan en `products/{nombre-producto}-{ulid}/{nombre-producto}-{descripcion}.webp`.
La descripción para el nombre es obligatoria y distinta entre las imágenes de un
producto; se comparan las versiones normalizadas y truncadas usadas en el archivo.
No se añade la posición. La carpeta es estable aunque cambie el nombre del producto.
El nombre del archivo se determina al cargar o reemplazar la imagen: editar el
producto, la descripción o el orden no renombra archivos existentes. Se permite
reemplazar el archivo de la misma imagen, pero cualquier otra colisión se informa
sin sobrescribir. Las cargas concurrentes requieren un almacén de caché compartido
que admita bloqueos. Al restaurar una copia de seguridad deben restaurarse tanto
la base de datos como los archivos correspondientes.

GD debe tener soporte WebP y EXIF en ambos entornos. Ambos archivos Compose usan
la imagen `fitopero/erp_php84_26:latest`, construida desde el `Dockerfile` de la
raíz. Para construir y publicar una actualización desde la raíz del proyecto:

```bash
docker build -t fitopero/erp_php84_26:latest .
docker login
docker push fitopero/erp_php84_26:latest
```

En cada VPS, después de actualizar el código y el Compose correspondiente,
descarga la imagen y recrea PHP. El ejemplo usa desarrollo; sustituye el nombre
del archivo por `docker-compose.prod.yml` en producción:

```bash
docker compose -f docker-compose.dev.yml pull php
docker compose -f docker-compose.dev.yml up -d --no-deps --force-recreate php
docker compose -f docker-compose.dev.yml exec php php -r 'var_export(gd_info()["WebP Support"]);'
```

La etiqueta `latest` es mutable: la imagen local no se actualiza hasta hacer
`pull`. Para despliegues reproducibles conviene publicar también una etiqueta
versionada. Las migraciones de creación se actualizaron para el esquema actual:
en una base ya migrada los cambios no aparecen automáticamente. Si se usa
`migrate:fresh`, se perderán todas las tablas y datos; respalda antes de hacerlo.

El seeder de productos descarga fotografías aleatorias de ejemplo desde
`picsum.photos` para cada producto sin imágenes, las convierte a WebP y las guarda
en el disco de imágenes configurado. Uno de cada cuatro productos recibe tres
imágenes; los demás, una. Los registros incluyen texto alternativo, título y
descripción de ejemplo. Las fotografías no representan necesariamente el producto,
por lo que estos textos no describen su contenido real y deben reemplazarse antes
de publicarlas. La ejecución requiere acceso a Internet y soporte WebP; si falla
la descarga o el guardado, el seeder informa el error. Al ejecutarlo de nuevo,
conserva las imágenes y metadatos ya asociados.

Las migraciones actuales crean el esquema de la galería desde cero. No convierten
ni importan las URLs antiguas; recrear una base existente elimina sus datos.

`product_images` guarda las rutas relativas y el orden: la primera imagen es la
principal. La API sigue entregando `image_link` y `additional_image_links` como
URLs calculadas para los clientes existentes. El formulario reordena las filas
secuencialmente; la base de datos usa el identificador como desempate si otro
proceso inserta posiciones iguales. La eliminación lógica conserva los archivos;
la eliminación definitiva de productos o categorías y los cambios de imágenes
limpian sus archivos después de confirmar la transacción. Las operaciones SQL
directas no ejecutan estos eventos, y las cargas abandonadas o fallidas pueden
dejar archivos huérfanos: supervisa y limpia estos casos con precaución.

El panel permite editar el texto alternativo, título y descripción de cada
imagen, los campos SEO de ambas webs y la descripción para MercadoLibre. Estos
datos se guardan en la base de datos, pero aún no generan etiquetas SEO ni se
envían automáticamente a los canales.

### Videos de productos

Las migraciones crean la galería desde cero y no importan enlaces externos
antiguos. Recrear una base existente elimina sus datos.

El panel permite cargar hasta diez videos por producto, ordenarlos y editar su
título y descripción. La descripción para el nombre del archivo es obligatoria
y distinta entre los videos del producto, con la misma normalización que las
imágenes. Editarla o reordenar no renombra archivos ya guardados. Acepta MP4,
MOV, WebM, MKV y AVI de hasta 100 MiB. Cada video se guarda como un único MP4
junto con las imágenes en `products/{nombre-producto}-{ulid}/`, con el nombre
`{nombre-producto}-{descripcion}.mp4`. Un MP4 se guarda sin tocarlo; los demás
formatos se convierten una sola vez a H.264/AAC con FFmpeg. No se generan
variantes ni portada.

Los nombres no incluyen la posición. Se permite reemplazar el mismo video;
una colisión con otra imagen, video o archivo se informa sin sobrescribirlo.

Reconstruye y despliega la imagen PHP como se indica arriba, y actualiza la
configuración de nginx en ambos entornos. Ajusta también los límites y tiempos
de cualquier proxy adicional. No se debe cambiar la imagen de un servidor en
uso sin coordinar su despliegue.

La API devuelve los videos ordenados con su `url`; `video_link` sigue disponible
como la URL del primer video. Los videos guardados antes de este cambio pueden
conservar archivos de variantes antiguas, que no se eliminan automáticamente. Las bajas
lógicas conservan los archivos y las definitivas los eliminan tras confirmar la
transacción, igual que con las imágenes. Las operaciones SQL directas y las
cargas abandonadas pueden dejar archivos huérfanos. El disco compartido con las
imágenes se configura con `PRODUCT_IMAGES_DISK` y debe respaldarse junto
con la base de datos. Cambiar de disco no migra los archivos.

Respalda regularmente `./ERP_storage/public` y la base de datos de cada VPS,
y prueba la restauración de ambos de forma coordinada.

Para un futuro uso de S3, instala el adaptador S3 compatible con la versión de
Laravel, configura las variables `AWS_*`, acceso público de lectura y
`PRODUCT_IMAGES_DISK=s3`, y verifica la vista previa y la publicación. Antes de
cambiar de disco, copia y verifica todos los archivos: cambiar la variable no
los migra. El proveedor debe permitir la visibilidad pública usada en las cargas.

## Verificación del correo de usuarios

Al crear un usuario se envía un enlace de verificación. Si se cambia su correo
en edición, se elimina la fecha de verificación y se envía un enlace nuevo a la
dirección guardada; los enlaces de la dirección anterior dejan de funcionar.
Los enlaces caducan después de 60 minutos.

En la edición de un usuario sin verificar, el botón **Reenviar correo de
verificación** envía el enlace al correo guardado. Guarda primero los cambios
del formulario. Si el envío falla, el usuario sigue sin verificar y el panel
avisa para que se pueda volver a enviar.

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
APP_URL=http://cap-erp-admin-dev.com
FRONTEND_URL=http://cap-erp-ecommerce-dev.com
SESSION_DOMAIN=cap-erp-admin-dev.com
SANCTUM_STATEFUL_DOMAINS=cap-erp-admin-dev.com,cap-erp-ecommerce-dev.com

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
VITE_HMR_HOST=cap-erp-admin-dev.com
VITE_HMR_PORT=5173
VITE_HMR_CLIENT_PORT=80
VITE_HMR_PROTOCOL=ws
VITE_HMR_PATH=/vite-hmr
VITE_DEV_SERVER_URL=http://cap-erp-admin-dev.com
```

**Volver al root:**
```bash
cd ..
```

### Paso 5: Iniciar Servicios Docker

# Login en dockerhub
docker login -u <nombre usuario>

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
- `Vue_ecommerce_app/src/**`

**Si necesitas instalar/actualizar dependencias:**

```bash
# Laravel - Instalar dependencias (dentro del contenedor node)
docker compose exec node sh -c "cd Laravel_app && npm install"

# Vue - Instalar dependencias (dentro del contenedor vue)
docker compose exec vue npm install

# O manualmente en tu máquina local (también válido):
cd Laravel_app && npm install && cd ..
cd Vue_ecommerce_app && npm install && cd ..
```

**Para compilar assets en producción (solo antes de deploy):**

```bash
# Compilar en tu máquina local, NO en servidor
cd Laravel_app
npm install
npm run build  # Genera Laravel_app/public/build/

cd ../Vue_ecommerce_app
npm install
npm run build  # Genera Vue_ecommerce_app/dist/
```

**Ver logs de los contenedores de frontend:**

```bash
# Ver logs de Vite (Laravel)
docker compose logs -f node

# Ver logs de Vue dev server
docker compose logs -f vue
```

**Hosts locales para desarrollo por dominio sin puerto:**

```text
# Estas entradas hacen que tu PC resuelva ambos dominios de desarrollo hacia la misma IP.
127.0.0.1 cap-erp-admin-dev.com
127.0.0.1 cap-erp-ecommerce-dev.com
```

**Hosts equivalentes si pruebas contra un servidor de desarrollo remoto:**

```text
# Sustituye `TU_IP_DEV` por la IP real del servidor donde corre el stack Docker de desarrollo.
TU_IP_DEV cap-erp-admin-dev.com
TU_IP_DEV cap-erp-ecommerce-dev.com
```

**Dominios finales esperados por Nginx:**

```text
# Producción usa estos dos dominios separados detrás del mismo Nginx.
https://cap-erp-admin.com
https://cap-erp-ecommerce.com
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
   - **Frontend Vue:** Modifica `Vue_ecommerce_app/src/**`
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
cd Vue_ecommerce_app
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
- `Vue_ecommerce_app/dist/` → Assets compilados de Vue

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
ls -lh Vue_ecommerce_app/dist/
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
cd Vue_ecommerce_app && npm install && npm run build && cd ..

# Subir solo los assets compilados al servidor
rsync -avz Laravel_app/public/build/ usuario@servidor:/ruta/proyecto/Laravel_app/public/build/
rsync -avz Vue_ecommerce_app/dist/ usuario@servidor:/ruta/proyecto/Vue_ecommerce_app/dist/
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

Vue_ecommerce_app/
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
