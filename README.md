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

## Sprint 3 — Comunidad por temas, comentarios y filtros (HU-17, HU-18, HU-04)

- **HU-17**: los espacios `/comunidad/{tema}` (rescatadas, reactivas, entrenamiento, salud, alimentación, adopción)
  ya permiten publicar. "Nueva publicación" pide apodo, título (máx. 120), texto (20 a 2000) y foto opcional
  (JPG/PNG/WEBP, máx. 5 MB). Las publicaciones se listan de la más reciente a la más antigua, 10 por página.
  Quien publica recibe un enlace privado `/publicacion/{token}` para editarla o eliminarla (sin login).
  Un tema que no está en `config/community.php` responde 404. La página principal muestra cuántas
  publicaciones tiene cada tema.
- **HU-18**: comentarios (apodo + 5 a 1000 caracteres) debajo de cada publicación, con su fecha, y botón
  **"Me sirvió"**. El servidor guarda una cookie por publicación (`helpful_post_{id}`) para que el mismo
  navegador no sume dos veces.
- **HU-04**: `/reportes` filtra por tipo, especie, color (catálogos de `config/pets.php`) y zona: con
  "Ver cerca de mí" se elige un radio de 1 a 20 km y los reportes se ordenan por cercanía. El listado y el mapa
  muestran lo mismo. Los filtros viajan en la URL (ej. `/reportes?type=perdida&species=gato&color=gris`)
  para compartir la búsqueda; si nada coincide aparece "No hay reportes con esos filtros" y "Limpiar filtros".

Tablas nuevas: `posts` y `comments` (migraciones `2026_10_13_*`).

> Las publicaciones y comentarios de `CommunitySeeder` son **datos de ejemplo ficticios**. En una base que ya
> tiene datos se pueden cargar solos con `php artisan db:seed --class=CommunitySeeder` (no hace nada si ya
> hay publicaciones).

Pruebas: `php artisan test` (incluye `tests/Feature/Sprint3Test.php`).

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