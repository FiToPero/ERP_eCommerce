# 🔐 Guía Completa: Configurar SSH para GitHub Actions

Esta guía explica paso a paso cómo configurar la conexión SSH desde GitHub Actions al servidor para deployments automáticos.

---

## 📍 Paso 1: Crear Usuario en el Servidor (Opcional pero Recomendado)

Conéctate a tu servidor y crea un usuario específico para deployments:

```bash
# Conectar a tu servidor
ssh root@tu-servidor.com

# Crear usuario 'deploy'
sudo adduser deploy

# Darle permisos de Docker (necesario para los deployments)
sudo usermod -aG docker deploy

# Si necesita ejecutar comandos sudo (opcional)
sudo usermod -aG sudo deploy

# Cambiar a ese usuario
sudo su - deploy
```

---

## 📍 Paso 2: Generar Claves SSH en el Servidor

Ahora, **como el usuario deploy** en el servidor, genera las claves:

```bash
# Generar clave SSH ed25519 (más segura que RSA)
ssh-keygen -t ed25519 -C "github-actions@tudominio.com" -f ~/.ssh/github_actions

# Cuando pregunte por passphrase, déjala VACÍA (solo presiona Enter)
```

Esto crea 2 archivos:
- `~/.ssh/github_actions` → Clave **PRIVADA** (para GitHub)
- `~/.ssh/github_actions.pub` → Clave **PÚBLICA** (para el servidor)

---

## 📍 Paso 3: Agregar Clave Pública al Servidor

```bash
# Agregar la clave pública a authorized_keys
cat ~/.ssh/github_actions.pub >> ~/.ssh/authorized_keys

# Configurar permisos correctos (MUY IMPORTANTE)
chmod 700 ~/.ssh
chmod 600 ~/.ssh/authorized_keys
chmod 600 ~/.ssh/github_actions

# Verificar permisos
ls -la ~/.ssh/
```

**Salida esperada:**
```
drwx------  2 deploy deploy  4096 Feb 22 00:00 .
-rw-------  1 deploy deploy   411 Feb 22 00:00 authorized_keys
-rw-------  1 deploy deploy   464 Feb 22 00:00 github_actions
-rw-r--r--  1 deploy deploy   103 Feb 22 00:00 github_actions.pub
```

---

## 📍 Paso 4: Copiar Clave PRIVADA

```bash
# Mostrar la clave PRIVADA (para copiarla)
cat ~/.ssh/github_actions
```

**Copia TODO el contenido**, desde `-----BEGIN OPENSSH PRIVATE KEY-----` hasta `-----END OPENSSH PRIVATE KEY-----` incluyendo esas líneas.

## 📍 Paso 5: Obtener Información del Servidor

```bash
# Ver usuario actual
whoami
# Ejemplo: deploy

# Ver ruta donde está el proyecto
pwd
# Ejemplo: /var/www/erp-filament

# Ver IP pública del servidor
curl ifconfig.me
# Ejemplo: 203.0.113.50

# O ver IP desde el servidor
hostname -I

# Verificar puerto SSH (normalmente 22)
ss -tlnp | grep ssh
# o verificar en: cat /etc/ssh/sshd_config | grep Port
```

**Resumen de la información que necesitas:**
- Usuario SSH: `deploy`
- IP/Host: `203.0.113.50` o `dev.tudominio.com`
- Puerto SSH: `22` (o el que uses)
- Path del proyecto: `/var/www/erp-filament`
- Clave privada: (copiada en el paso anterior)

---

## 🌐 Paso 6: Configurar Secrets en GitHub (Interfaz Web)

### A. Acceder a la configuración de secrets:

1. Ve a tu repositorio en GitHub: `https://github.com/tu-usuario/tu-repositorio`
2. Click en **Settings** (pestaña arriba a la derecha)
3. En el menú izquierdo, busca la sección **Security**
4. Click en **Secrets and variables**
5. Click en **Actions**
6. Click en botón verde **New repository secret**

### B. Agregar cada secret uno por uno:

#### Para Ambiente de Desarrollo (branch: develop)

**Secret 1: DEV_SSH_HOST**
```
Name: DEV_SSH_HOST
Secret: 203.0.113.50
```
o tu dominio: `dev.tudominio.com`

Click en **Add secret**

---

**Secret 2: DEV_SSH_USER**
```
Name: DEV_SSH_USER
Secret: deploy
```

Click en **Add secret**

---

**Secret 3: DEV_SSH_KEY** ⚠️ **MUY IMPORTANTE**
```
Name: DEV_SSH_KEY
Secret: -----BEGIN OPENSSH PRIVATE KEY-----
b3BlbnNzaC1rZXktdjEAAAAABG5vbmUAAAAEbm9uZQAAAAAAAAABAAAAMwAAAA...
[TODA la clave privada que copiaste del paso 4]
...
-----END OPENSSH PRIVATE KEY-----
```

⚠️ **Puntos críticos:**
- Pega TODA la clave, incluyendo `-----BEGIN...` y `-----END...`
- NO agregues espacios al inicio o final
- NO modifiques el formato de las líneas
- Debe ser EXACTAMENTE como sale de `cat ~/.ssh/github_actions`

Click en **Add secret**

---

**Secret 4: DEV_SSH_PORT** (Opcional - solo si NO es 22)
```
Name: DEV_SSH_PORT
Secret: 22
```

Solo configúralo si tu servidor SSH usa un puerto diferente al 22.

Click en **Add secret**

---

**Secret 5: DEV_APP_PATH**
```
Name: DEV_APP_PATH
Secret: /home/fito/ERP_develop
```

El path absoluto donde está clonado tu proyecto en el servidor.

Click en **Add secret**

---

#### Para Ambiente de Producción (branch: main)

Repite el mismo proceso para los secrets de producción. **IMPORTANTE:** Genera una clave SSH DIFERENTE en el servidor de producción.

**En el servidor de producción:**
```bash
# Conectar al servidor de producción
ssh root@servidor-produccion.com

# Crear/cambiar a usuario deploy
sudo su - deploy

# Generar OTRA clave SSH (diferente a dev)
ssh-keygen -t ed25519 -C "github-actions-prod@tudominio.com" -f ~/.ssh/github_actions

# Agregar clave pública
cat ~/.ssh/github_actions.pub >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys

# Copiar clave PRIVADA
cat ~/.ssh/github_actions
```

**Secrets de producción a configurar en GitHub:**

- `PROD_SSH_HOST` → IP o dominio del servidor de producción
- `PROD_SSH_USER` → Usuario SSH (ejemplo: `deploy`)
- `PROD_SSH_KEY` → Clave privada del servidor de producción (DIFERENTE a dev)
- `PROD_SSH_PORT` → Puerto SSH (22 si es default)
- `PROD_APP_PATH` → Path del proyecto (ejemplo: `/var/www/erp-filament`)
- `PROD_DOMAIN` → Dominio completo (ejemplo: `erp.tuempresa.com`)

---

## ✅ Verificar Configuración

### Prueba 1: Conexión SSH Manual

Desde tu máquina local, verifica que puedes conectarte:

```bash
# Prueba conectarte al servidor usando la clave
ssh -i ~/.ssh/github_actions deploy@tu-servidor.com

# Si no pide contraseña y te deja entrar, ¡está correcto! ✅
```

### Prueba 2: Verificar Secrets en GitHub

1. Ve a **Settings → Secrets and variables → Actions**
2. Deberías ver tus secrets listados (sin poder ver su contenido)
3. Verifica que los nombres están escritos EXACTAMENTE como en los workflows:
   - `DEV_SSH_HOST`
   - `DEV_SSH_USER`
   - `DEV_SSH_KEY`
   - `DEV_APP_PATH`
   - etc.

### Prueba 3: Trigger del Workflow

```bash
# Para ambiente de desarrollo
git checkout develop
git add .
git commit -m "Test: Verificar conexión SSH"
git push origin develop

# Luego ve a GitHub → Actions tab y observa el workflow ejecutarse
```

Si hay errores, revisa los logs en **GitHub → Actions → Click en el workflow → Click en el job que falló**

---

## 🔍 Troubleshooting: Problemas Comunes

### Error: "Permission denied (publickey)"

**Causa:** La clave pública no está en `authorized_keys` o los permisos son incorrectos.

**Solución:**
```bash
# En el servidor
chmod 700 ~/.ssh
chmod 600 ~/.ssh/authorized_keys
cat ~/.ssh/github_actions.pub >> ~/.ssh/authorized_keys
```

### Error: "Host key verification failed"

**Causa:** GitHub Actions no conoce la huella digital del servidor.

**Solución:** Agregar `StrictHostKeyChecking=no` en el workflow (YA ESTÁ configurado en el archivo `develop.yml` y `production.yml`)

### Error: "Command not found: docker"

**Causa:** El usuario no tiene acceso a Docker.

**Solución:**
```bash
# En el servidor
sudo usermod -aG docker deploy
# Luego cierra sesión y vuelve a entrar
logout
sudo su - deploy
docker ps  # Debe funcionar sin sudo
```

### Error: La clave SSH no funciona

**Checklist:**
- [ ] Clave privada copiada COMPLETA (incluye headers)
- [ ] Clave pública está en `authorized_keys`
- [ ] Permisos: `~/.ssh` es 700
- [ ] Permisos: `authorized_keys` es 600
- [ ] Permisos: `github_actions` es 600
- [ ] No hay espacios extra al pegar en GitHub
- [ ] El nombre del secret es EXACTO (case-sensitive)

---

## 📋 Checklist Final

### En el Servidor:
- [ ] Usuario `deploy` creado
- [ ] Usuario tiene permisos de Docker (`docker ps` funciona)
- [ ] Claves SSH generadas (`ls ~/.ssh/github_actions*`)
- [ ] Clave pública agregada a `authorized_keys`
- [ ] Permisos correctos: `.ssh` = 700, archivos = 600
- [ ] Proyecto clonado en el path correcto
- [ ] Puedes conectarte por SSH sin contraseña

### En GitHub:
- [ ] Clave privada copiada COMPLETA
- [ ] Todos los secrets configurados:
  - [ ] `DEV_SSH_HOST` / `PROD_SSH_HOST`
  - [ ] `DEV_SSH_USER` / `PROD_SSH_USER`
  - [ ] `DEV_SSH_KEY` / `PROD_SSH_KEY`
  - [ ] `DEV_APP_PATH` / `PROD_APP_PATH`
  - [ ] `PROD_DOMAIN` (solo producción)
- [ ] Nombres escritos EXACTAMENTE como en workflows
- [ ] Push a `develop` o `main` ejecuta el workflow

---

## 🎯 Resumen Rápido

```bash
# 1. En el servidor
ssh root@servidor.com
sudo adduser deploy
sudo usermod -aG docker deploy
sudo su - deploy

# 2. Generar claves
ssh-keygen -t ed25519 -C "github@servidor.com" -f ~/.ssh/github_actions
cat ~/.ssh/github_actions.pub >> ~/.ssh/authorized_keys
chmod 700 ~/.ssh && chmod 600 ~/.ssh/*

# 3. Copiar clave privada
cat ~/.ssh/github_actions
# [Copiar TODO el contenido]

# 4. Obtener info
whoami                # Usuario
pwd                   # Path del proyecto
curl ifconfig.me      # IP del servidor

# 5. En GitHub Web
# Settings → Secrets and variables → Actions → New repository secret
# Agregar: DEV_SSH_HOST, DEV_SSH_USER, DEV_SSH_KEY, DEV_APP_PATH

# 6. Probar
git push origin develop
# Ver: GitHub → Actions tab
```

---

## 📚 Referencias

- Documentación SSH: [OpenSSH Manual](https://www.openssh.com/manual.html)
- GitHub Actions SSH: [appleboy/ssh-action](https://github.com/appleboy/ssh-action)
- GitHub Secrets: [Encrypted secrets](https://docs.github.com/en/actions/security-guides/encrypted-secrets)

---

## ⚠️ Seguridad

**Mejores prácticas:**

1. **Usa claves DIFERENTES para dev y producción**
2. **NUNCA compartas las claves privadas** por email, Slack, etc.
3. **Rota las claves cada 3-6 meses**
4. **No uses el usuario root** - crea un usuario específico
5. **Limita permisos** - solo lo necesario para deploy
6. **Cambia el puerto SSH** del default 22 si es posible
7. **Usa fail2ban** para prevenir ataques de fuerza bruta
8. **Backups de los secrets** en un gestor de contraseñas del equipo

**Si una clave se compromete:**
```bash
# 1. En el servidor, elimina la clave pública
nano ~/.ssh/authorized_keys
# Borrar la línea con la clave comprometida

# 2. Genera nuevas claves
ssh-keygen -t ed25519 -C "github-new@servidor.com" -f ~/.ssh/github_actions_new

# 3. Actualiza los secrets en GitHub
```

---

¿Necesitas ayuda con algún paso específico? Revisa el README_CICD.md para más detalles sobre el flujo completo de CI/CD.
