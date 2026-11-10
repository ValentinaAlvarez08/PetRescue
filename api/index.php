<?php

// Punto de entrada en Vercel. La función vive en /api/index.php y, sin esto,
// Laravel toma "/api" como carpeta base y lo quita de la ruta: entonces
// /api/reportes/cercanos se atendería como /reportes/cercanos (404).
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['PHP_SELF'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = __DIR__.'/../public/index.php';

require __DIR__.'/../public/index.php';
