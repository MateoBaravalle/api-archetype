<?php

declare(strict_types=1);

use Laravel\Sanctum\Sanctum;

return [
    /*
    |--------------------------------------------------------------------------
    | Stateful Domains
    |--------------------------------------------------------------------------
    |
    | Aquí puedes definir los dominios donde las cookies de Laravel Sanctum
    | deberían estar disponibles. Por lo general, esto incluye tu dominio
    | local y cualquier dominio que necesite acceso a tu API mediante
    | autenticación basada en cookies.
    |
    */

    'stateful' => explode(',', env(
        'SANCTUM_STATEFUL_DOMAINS',
        sprintf(
            '%s%s',
            'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
            env('APP_URL') ? ','.parse_url(env('APP_URL'), PHP_URL_HOST) : ''
        )
    )),

    /*
    |--------------------------------------------------------------------------
    | Sanctum Guards
    |--------------------------------------------------------------------------
    |
    | Este array contiene los guards de autenticación que serán utilizados por
    | Sanctum cuando autentique usuarios. Puedes modificar estos valores para
    | especificar múltiples guard.
    |
    */

    'guard' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Expiration Minutes
    |--------------------------------------------------------------------------
    |
    | Este valor determina la cantidad de minutos que un token de acceso
    | debe considerarse válido. Esto controla por cuánto tiempo un usuario
    | puede permanecer inactivo antes de que se requiera reautenticación.
    | El valor predeterminado de 0 significa "sin expiración".
    |
    */

    'expiration' => 60 * 24, // 24 horas

    /*
    |--------------------------------------------------------------------------
    | Token Prefix
    |--------------------------------------------------------------------------
    |
    | Sanctum permite usar cualquier string como prefijo para los tokens de
    | acceso personal. Esto puede ser útil cuando tienes múltiples aplicaciones
    | que usan Sanctum y quieres asegurarte de que un token de una aplicación
    | no pueda ser usado en otra.
    |
    */

    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),

    /*
    |--------------------------------------------------------------------------
    | Sanctum Middleware
    |--------------------------------------------------------------------------
    |
    | Cuando autenticas usuarios con tokens, puedes especificar qué middleware
    | debe ejecutarse antes de que la solicitud llegue a tu controlador.
    | Esto permite verificar el token, etc.
    |
    */

    'middleware' => [
        'verify_csrf_token' => App\Http\Middleware\VerifyCsrfToken::class,
        'encrypt_cookies' => App\Http\Middleware\EncryptCookies::class,
    ],
];
