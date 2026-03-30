# Checking_command_Deploy

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

## 4. Ejecutar tests (opcional)

```bash
docker compose exec php php artisan test
```
## 5. Pruebas funcionales básicas

- Realiza acciones clave (login, navegación, formularios).