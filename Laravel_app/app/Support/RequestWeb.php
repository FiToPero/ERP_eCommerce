<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RequestWeb
{
    // Origen de la petición (ej. https://cap-erp-ecommerce-dev.com): header Origin o, si falta, el Referer
    public static function origin(Request $request): ?string
    {
        $origin = $request->headers->get('Origin') ?: $request->headers->get('Referer');

        if (! $origin) {
            return null;
        }

        $scheme = parse_url($origin, PHP_URL_SCHEME);
        $host   = parse_url($origin, PHP_URL_HOST);
        $port   = parse_url($origin, PHP_URL_PORT);

        if (! $scheme || ! $host) {
            return null;
        }

        return Str::limit($scheme.'://'.$host.($port ? ':'.$port : ''), 255, '');
    }
}
