# Checking_command_Deploy

Guía rápida para verificar que el deploy fue correcto y todo funciona en el servidor.

---

## 1. Verificar commit

```bash
git log --oneline -1
```
- Confirma que el último commit corresponde al que subiste.

## 2. Verificar contenedores activos

```bash
docker compose ps
```
- Todos los servicios deben estar "Up".

## 3. Revisar logs de contenedores

```bash
docker compose logs --tail=50
```
- Busca errores recientes.

## 4. Acceso a la aplicación

- Abre la URL del servidor en el navegador.
- Comprueba que los cambios estén visibles.

## 5. Pruebas funcionales básicas

- Realiza acciones clave (login, navegación, formularios).

## 6. Ejecutar tests (opcional)

```bash
docker compose exec php php artisan test
```
- Verifica que los tests pasen.

---

**Recomendación:** Automatiza estos pasos en un script si lo necesitas.

---

> Si algún paso falla, revisa los logs y la configuración del workflow.
