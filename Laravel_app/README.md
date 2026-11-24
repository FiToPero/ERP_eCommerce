<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Asignación inicial de permisos

`RolePermissionSeeder` agrega las asociaciones de permisos a roles indicadas por sus IDs, sin eliminar permisos existentes. Se ejecuta desde `DatabaseSeeder` después de generar los permisos de Shield y crear los roles, o individualmente con `php artisan db:seed --class=RolePermissionSeeder`.

Antes de ejecutarlo, verifica que los IDs 1 a 6 de roles y 1 a 69 de permisos correspondan a los mismos roles y permisos de la base de datos de referencia. Los IDs pueden cambiar entre bases de datos; la existencia de un ID no garantiza que represente el mismo rol o permiso. Si falta alguno, el seeder falla sin guardar asignaciones parciales.

## Pruebas de autenticación y usuarios

Las pruebas usan PHPUnit, Livewire y Filament instalados por Composer. `phpunit.xml` configura SQLite en memoria con claves foráneas, correo y caché en memoria, cola síncrona y sesiones en memoria. `tests/TestCase.php` comprueba esta configuración antes de ejecutar migraciones y aborta si no está aislada. No uses una base de desarrollo o producción para pruebas.

Desde la raíz del repositorio, con el servicio PHP de desarrollo ejecutándose:

```bash
docker compose -f docker-compose.dev.yml exec -w /var/www/html/Laravel_app php php artisan test
```

Para un archivo específico, añade su ruta al comando, por ejemplo `tests/Feature/UserManagementTest.php`. El entorno `testing` se establece también en `$_SERVER`, aunque el contenedor herede `APP_ENV=local`. En un entorno compartido evita el script de Composer que ejecuta `config:clear`. Si existe configuración cacheada o variables de entorno incompatibles, corrige el entorno de pruebas de forma segura: la comprobación de aislamiento impedirá ejecutarlas. El contenedor debe tener `pdo_sqlite`.

Las pruebas cubren el panel de usuarios y la API de clientes, no la interfaz Vue. Los correos se simulan; las pruebas de transacciones se ejecutan en procesos separados para verificar commits y rollbacks reales. SQLite no sustituye una futura suite aislada con el motor de producción; el guard actual limita explícitamente estas pruebas a SQLite en memoria.

En la configuración actual, Filament limita el login a cinco intentos y la solicitud y el restablecimiento de contraseña a dos intentos mediante Livewire. Las rutas de verificación y reenvío de la API y la ruta de verificación del panel usan `throttle:6,1`. Las rutas de login y recuperación de la API no declaran un límite propio; cualquier protección adicional de infraestructura debe verificarse por separado.

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
