# PetRescue

Plataforma para reportar y reencontrar mascotas perdidas, sin necesidad de registro, con notificaciones por cercanía.

## Sprint 1 — HU1, HU2, HU3

- **HU1**: reportar mascota perdida sin necesidad de crear cuenta.
- **HU2**: reportar mascota encontrada sin necesidad de crear cuenta.
- **HU3**: consulta de reportes cercanos por geolocalización.

Cada reporte genera un enlace privado con token (UUID) que su autor usa para
darle seguimiento, evitando la necesidad de login.

## Requisitos

- PHP 8.2+, Composer
- MySQL

## Instalación local

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Configura `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD` en `.env`, luego:

```bash
php artisan storage:link
php artisan migrate
php artisan serve
```

Abre `http://127.0.0.1:8000`.
