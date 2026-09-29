# PetRescue

Plataforma para reportar y reencontrar mascotas perdidas, sin necesidad de registro, con notificaciones por cercanía.

## Sprint 1 — HU1, HU2, HU3

- **HU1**: reportar mascota perdida sin necesidad de crear cuenta.
- **HU2**: reportar mascota encontrada sin necesidad de crear cuenta.
- **HU3**: consulta de reportes cercanos por geolocalización.

Cada reporte genera un enlace privado con token (UUID) que su autor usa para
darle seguimiento, evitando la necesidad de login.

## Sprint 2 — Comunidad (HU-13, HU-14, HU-15, HU-16, HU-06)

- **HU-13**: formulario guiado en 3 pasos. Solo permite perros, gatos, conejos, aves y hámsteres/cobayos;
  raza según la especie, colores predefinidos (máx. 3), sexo, tamaño (perros) y fecha de los últimos 90 días.
- **HU-14**: el lugar donde se perdió o encontró se marca en un mapa (clic, arrastrar el pin, buscar dirección
  o "usar mi ubicación"). Mapas con Leaflet + OpenStreetMap: **ya no se necesita API key de Google Maps**.
- **HU-15**: página principal de la comunidad (bienvenida, directorio, temas de conversación, reportes recientes).
- **HU-16**: directorio de servicios en `/directorio` (tiendas, guarderías, baño y peluquería, veterinarias,
  droguerías). Al tocar un lugar se ve a qué se dedica, horario, contacto, WhatsApp y "cómo llegar".
- **HU-06**: contacto directo desde el reporte (WhatsApp, llamar, correo, compartir).
- **Seguridad**: el listado y los avisos por correo usan el enlace público `/reportes/{id}`; el enlace privado
  `/reporte/{token}` solo lo recibe quien publicó el reporte.

> Los negocios de `BusinessSeeder` son **datos de ejemplo ficticios**. Reemplácenlos por negocios reales antes de publicar.

Pruebas: `php artisan test` (incluye `tests/Feature/Sprint2Test.php`).

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
php artisan migrate --seed
php artisan serve
```

Abre `http://127.0.0.1:8000`.

## Colaboradores
- Valentina Alvarez Solarte
- Juan David Delgado Muñoz
- Carlos Andres Quenan Alderete
- Victor Manuel Velasquez Benavides