<?php

/*
|--------------------------------------------------------------------------
| Punto de entrada en Vercel
|--------------------------------------------------------------------------
| En Vercel el código es de solo lectura; solo se puede escribir en /tmp.
| Por eso las vistas compiladas, las cachés de arranque y los logs se
| mueven ahí antes de cargar Laravel. Localmente no se usa este archivo.
*/

$tmp = '/tmp/laravel';

foreach ([
    "$tmp/storage/app/public",
    "$tmp/storage/framework/cache/data",
    "$tmp/storage/framework/sessions",
    "$tmp/storage/framework/views",
    "$tmp/storage/logs",
    "$tmp/bootstrap/cache",
] as $dir) {
    if (! is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
}

$vars = [
    'LARAVEL_STORAGE_PATH' => "$tmp/storage",
    'VIEW_COMPILED_PATH' => "$tmp/storage/framework/views",
    'APP_CONFIG_CACHE' => "$tmp/bootstrap/cache/config.php",
    'APP_EVENTS_CACHE' => "$tmp/bootstrap/cache/events.php",
    'APP_PACKAGES_CACHE' => "$tmp/bootstrap/cache/packages.php",
    'APP_ROUTES_CACHE' => "$tmp/bootstrap/cache/routes.php",
    'APP_SERVICES_CACHE' => "$tmp/bootstrap/cache/services.php",
];

foreach ($vars as $key => $value) {
    putenv("$key=$value");
    $_ENV[$key] = $_SERVER[$key] = $value;
}

require __DIR__.'/../public/index.php';
