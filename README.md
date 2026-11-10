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

## Sprint 4 — Coincidencias, edición, denuncias, perfil digital, negocios y guías (HU-05, HU-08, HU-07, HU-20, HU-10, HU-19, HU-21)

- **HU-05**: cada reporte activo muestra los reportes del tipo contrario que podrían ser la misma mascota (misma especie,
  a menos de 10 km y con fechas compatibles), con un puntaje de parecido y las razones ("Mismo color", "A 1,2 km"...).
  El puntaje suma distancia, colores, raza, sexo, tamaño y fechas (`App\Support\ReportMatcher`, ajustes en
  `config('pets.matching')`). En el último paso del formulario se consultan antes de publicar (`/api/reportes/similares`).
- **HU-08**: al publicar o editar un reporte, quien publicó un reporte parecido recibe un correo con la foto, el
  puntaje y el enlace público. Solo si dejó correo, una sola vez por par (tabla `report_matches`) y no si ambos
  reportes tienen el mismo celular.
- **HU-07**: desde el enlace privado se editan los datos, la foto (opcional), el lugar y el contacto
  (`/reporte/{token}/editar`); el tipo y la especie no cambian. También se puede cerrar y **reabrir** el reporte;
  uno cerrado no se edita hasta reabrirlo.
- **HU-20**: reportes, publicaciones y comentarios tienen "🚩 Denunciar" con un motivo (`config('community.flags')`).
  Cada persona cuenta una vez (se guarda un hash de la IP, no la IP). Con 3 denuncias el contenido se oculta y se
  avisa a moderación con un enlace firmado para volver a mostrarlo.
- **HU-10**: perfil digital de la mascota en `/mi-mascota/nueva` (foto, señas, salud y contacto). El enlace público
  `/mascota/{código}` usa un código corto (no el id) y la placa con **QR** se imprime desde el enlace privado.
  "Reportar que se perdió" crea el reporte con los datos y la foto del perfil, y el perfil público muestra
  "¡Está perdida!".
- **HU-19**: "Registrar mi negocio" en `/directorio/registrar`. La solicitud queda **pendiente** (no aparece en el
  directorio) hasta que moderación la apruebe o rechace con el enlace firmado del correo; quien la pidió recibe
  un correo en cada paso. No se acepta un nombre que ya esté en el directorio o en revisión.
- **HU-21**: guías rápidas en `/guias` (perdidas, emergencias, salud, alimentación y convivencia), con filtro por
  tema, búsqueda y pasos numerados. El contenido está en `config/guides.php` y las de salud recomiendan siempre
  consultar al veterinario.

**Moderación sin login**: configura `MODERATION_EMAIL` en `.env` para recibir las solicitudes de negocios y los
avisos de contenido oculto. Los enlaces de esos correos están firmados y vencen a los 30 días. Sin correo, o para
obtener enlaces nuevos, usa `php artisan moderacion:pendientes`.

Tablas nuevas: `pet_profiles`, `report_matches` y `content_flags` (migraciones `2026_10_27_*`).

Pruebas: `php artisan test` (incluye `tests/Feature/Sprint4Part2Test.php`).

## Arquitectura y principios SOLID

El código separa **qué se necesita** (interfaces en `app/Contracts`) de **cómo se hace** (servicios en
`app/Services`). `AppServiceProvider::register()` decide qué implementación usar, así que cambiar una
(por ejemplo guardar fotos en S3 u otro algoritmo de coincidencias) solo toca ese archivo.

| Contrato (`app/Contracts`) | Implementación | Quién lo usa |
|---|---|---|
| `ReportMatcher` | `Services/Matching/WeightedReportMatcher` | `PetReportController`, `Api\PetReportApiController`, `NotifyPossibleMatches`, `PetReport::similarReports()` |
| `MatchCriterion` | `Services/Matching/Criteria/*` (distancia, colores, raza, sexo, tamaño, fechas) | `WeightedReportMatcher` |
| `DistanceCalculator` | `Services/Geo/HaversineDistance` | `NearbyFinder`, `AlertZone::covering()` |
| `PhotoStorage` | `Services/Photos/PublicDiskPhotoStorage` | controladores de reportes, perfiles y publicaciones |
| `Flaggable` | `PetReport`, `Post`, `Comment` (con el trait `Models/Concerns/Flaggable`) | `FlagController`, `ModerationController`, `ContentHiddenNotification` |

- **S — Responsabilidad única**: validación en *Form Requests* (`SimilarReportsRequest`, `NearbyReportsRequest`…),
  filtros del listado en `Http/Filters/ReportFilters`, búsqueda por cercanía en `Services/Geo/NearbyFinder`,
  avisos a moderación en `Services/Moderation/ModerationNotifier`, efectos secundarios en eventos y listeners.
  La API JSON está en su propio controlador (`Api\PetReportApiController`).
- **O — Abierto/cerrado**: el puntaje de coincidencias es la suma de criterios con pesos en
  `config('pets.matching.weights')`. Para agregar un criterio se crea una clase que implemente `MatchCriterion`
  y se registra en `AppServiceProvider`, sin modificar el algoritmo. Para hacer denunciable un modelo nuevo basta
  con implementar `Flaggable` y agregarlo al morph map.
- **L — Sustitución de Liskov**: cualquier `Flaggable` se denuncia, oculta y restaura igual; cualquier
  `ReportMatcher` o `PhotoStorage` puede reemplazar al actual (las pruebas lo hacen con implementaciones falsas).
- **I — Segregación de interfaces**: contratos pequeños, de uno a seis métodos, cada uno con un solo propósito.
- **D — Inversión de dependencias**: controladores, listeners y servicios reciben interfaces por el constructor.

`NearbyFinder` descarta primero en SQL lo que está fuera del cuadrado que rodea el radio y después calcula la
distancia exacta, así no se cargan todos los reportes en memoria.

Pruebas de la arquitectura: `tests/Feature/ArchitectureTest.php`.

## Seguridad

- **Límite de envíos por IP** (no hay login): publicar reportes, perfiles, publicaciones, negocios y suscripciones
  10/min; comentarios y "Me sirvió" 30/min; denuncias 10/min; API JSON 60/min. Se ajustan en
  `AppServiceProvider::configureRateLimiting()`.
- Las credenciales van solo en `.env` (ignorado por git). Nunca subas archivos con contraseñas.

## Correos y colas

Los correos se envían en cola. Con `QUEUE_CONNECTION=database` deja corriendo `php artisan queue:work`;
en un hosting sin worker (por ejemplo Vercel) usa `QUEUE_CONNECTION=sync`.

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