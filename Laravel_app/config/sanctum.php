<?php

use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Laravel\Sanctum\Http\Middleware\AuthenticateSession;
use Laravel\Sanctum\Sanctum;

// Este helper extrae sólo el host de una URL para poder construir dominios stateful a partir de `APP_URL` y `FRONTEND_URL`.
$extractHost = static function (?string $url): ?string {
    // Si la URL viene vacía devolvemos `null` para que luego pueda filtrarse sin errores.
    if (! $url) {
        // Este retorno evita intentar parsear valores inexistentes.
        return null;
    }

    // Este parse obtiene el host sin protocolo ni path de la URL configurada.
    $host = parse_url($url, PHP_URL_HOST);

    // Este retorno final deja sólo strings válidos o `null` si `parse_url` no encontró host.
    return is_string($host) ? $host : null;
};

// Esta colección reúne todos los hosts de primera parte que deberían ser stateful por defecto.
$defaultStatefulDomains = array_values(array_unique(array_filter([
    // Este valor mantiene `localhost` para escenarios manuales fuera de Docker.
    'localhost',
    // Este valor conserva `127.0.0.1` para pruebas locales directas.
    '127.0.0.1',
    // Este valor conserva el loopback IPv6 para entornos que lo utilicen.
    '::1',
    // Este host deriva del `APP_URL` para incluir automáticamente el dominio del admin actual.
    $extractHost(env('APP_URL')),
    // Este host deriva de `FRONTEND_URL` para incluir automáticamente el dominio del frontend separado.
    $extractHost(env('FRONTEND_URL')),
    // Este helper de Sanctum mantiene compatibilidad con el dominio actual de la aplicación y su puerto.
    Sanctum::currentApplicationUrlWithPort(),
])));

return [

    /*
    |--------------------------------------------------------------------------
    | Stateful Domains
    |--------------------------------------------------------------------------
    |
    | Requests from the following domains / hosts will receive stateful API
    | authentication cookies. Typically, these should include your local
    | and production domains which access your API via a frontend SPA.
    |
    */

    // Esta lista usa primero la variable explícita y, si falta, construye defaults útiles a partir de `APP_URL` y `FRONTEND_URL`.
    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', implode(',', $defaultStatefulDomains))),

    /*
    |--------------------------------------------------------------------------
    | Sanctum Guards
    |--------------------------------------------------------------------------
    |
    | This array contains the authentication guards that will be checked when
    | Sanctum is trying to authenticate a request. If none of these guards
    | are able to authenticate the request, Sanctum will use the bearer
    | token that's present on an incoming request for authentication.
    |
    */

    'guard' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Expiration Minutes
    |--------------------------------------------------------------------------
    |
    | This value controls the number of minutes until an issued token will be
    | considered expired. This will override any values set in the token's
    | "expires_at" attribute, but first-party sessions are not affected.
    |
    */

    'expiration' => null,

    /*
    |--------------------------------------------------------------------------
    | Token Prefix
    |--------------------------------------------------------------------------
    |
    | Sanctum can prefix new tokens in order to take advantage of numerous
    | security scanning initiatives maintained by open source platforms
    | that notify developers if they commit tokens into repositories.
    |
    | See: https://docs.github.com/en/code-security/secret-scanning/about-secret-scanning
    |
    */

    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),

    /*
    |--------------------------------------------------------------------------
    | Sanctum Middleware
    |--------------------------------------------------------------------------
    |
    | When authenticating your first-party SPA with Sanctum you may need to
    | customize some of the middleware Sanctum uses while processing the
    | request. You may change the middleware listed below as required.
    |
    */

    'middleware' => [
        'authenticate_session' => AuthenticateSession::class,
        'encrypt_cookies' => EncryptCookies::class,
        'validate_csrf_token' => ValidateCsrfToken::class,
    ],

];
