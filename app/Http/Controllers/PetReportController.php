<?php

namespace App\Http\Controllers;

use App\Events\PetReportPublished;
use App\Events\PetReportUpdated;
use App\Http\Requests\StorePetReportRequest;
use App\Http\Requests\UpdatePetReportRequest;
use App\Models\PetProfile;
use App\Models\PetReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PetReportController extends Controller
{
    /** HU-04: radios que se ofrecen en el filtro (km). */
    private const RADIUS_OPTIONS = [1, 2, 5, 10, 20];

    /**
     * Listado público. Si llegan lat/lng, se muestran ordenados por
     * cercanía (HU3). Sin esos parámetros, se listan los más recientes.
     *
     * Sprint 3 — HU-04: filtros por tipo, especie, color y radio (1 a 20 km).
     * Todos viajan en la URL (query string) para poder compartir la búsqueda;
     * un valor que no exista en config/pets.php simplemente se ignora.
     */
    public function index(Request $request): View
    {
        $filters = $this->reportFilters($request);
        $lat = is_numeric($request->query('lat')) ? (float) $request->query('lat') : null;
        $lng = is_numeric($request->query('lng')) ? (float) $request->query('lng') : null;
        $radius = $filters['radius'];

        $query = PetReport::query()->active();

        if ($filters['type']) {
            $query->ofType($filters['type']);
        }
        if ($filters['species']) {
            $query->where('species', $filters['species']);
        }
        if ($filters['color']) {
            $query->withColor($filters['color']);
        }

        if ($lat !== null && $lng !== null) {
            $reports = PetReport::near($query, (float) $lat, (float) $lng, $radius);
        } else {
            $reports = $query->latest()->get();
        }

        $mapReports = $reports->map(fn (PetReport $report) => [
            'lat' => (float) $report->latitude,
            'lng' => (float) $report->longitude,
            'type' => $report->type,
            'status' => $report->status,
            'pet_name' => $report->display_name,
            'species' => $report->species_label,
            'emoji' => $report->species_emoji,
            'photo_url' => $report->photo_url,
            'url' => route('reports.show', $report),
        ])->values();

        return view('pet_reports.index', [
            'reports' => $reports,
            'mapReports' => $mapReports,
            'lat' => $lat,
            'lng' => $lng,
            'radius' => $radius,
            'type' => $filters['type'],
            'filters' => $filters,
            'hasFilters' => $filters['type'] || $filters['species'] || $filters['color'] || $lat !== null,
            'speciesCatalog' => config('pets.species'),
            'colorsCatalog' => config('pets.colors'),
            'radiusOptions' => self::RADIUS_OPTIONS,
        ]);
    }

    /**
     * HU-04: lee los filtros de la URL y descarta los valores que no están
     * en los catálogos. El radio se limita a 1–20 km.
     */
    private function reportFilters(Request $request): array
    {
        $pick = fn (string $key, array $allowed) => in_array($request->query($key), $allowed, true) ? $request->query($key) : null;

        $radius = is_numeric($request->query('radius')) ? (float) $request->query('radius') : 5.0;

        return [
            'type' => $pick('type', ['perdida', 'encontrada']),
            'species' => $pick('species', array_keys(config('pets.species'))),
            'color' => $pick('color', array_keys(config('pets.colors'))),
            'radius' => min(20.0, max(1.0, $radius)),
        ];
    }

    /**
     * HU1 / HU2: formulario de reporte (perdida o encontrada), sin login.
     * HU-10 (CID 4): con ?perfil={token} se prellena con el perfil digital.
     */
    public function create(Request $request, string $type): View
    {
        abort_unless(in_array($type, ['perdida', 'encontrada'], true), 404);

        $profile = $type === 'perdida' && is_string($request->query('perfil'))
            ? PetProfile::where('management_token', $request->query('perfil'))->first()
            : null;

        return view('pet_reports.create', [
            'type' => $type,
            'species' => config('pets.species'),
            'colors' => config('pets.colors'),
            'sizes' => config('pets.sizes'),
            'sexes' => config('pets.sexes'),
            'profile' => $profile,
            'prefill' => $profile?->toReportPrefill() ?? [],
        ]);
    }

    public function store(StorePetReportRequest $request): RedirectResponse
    {
        $data = $request->reportData();

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('pet-reports', 'public');
        } elseif ($profile = $request->profile()) {
            $data['photo_path'] = $this->copyProfilePhoto($profile);
        }

        $report = PetReport::create($data);

        // HU3: avisa automáticamente a los suscriptores cercanos.
        PetReportPublished::dispatch($report);

        // El "enlace de gestión" reemplaza el login: es lo único que
        // necesita el usuario para editar o cerrar su propio caso.
        return redirect()
            ->route('reports.manage', $report->management_token)
            ->with('status', 'Reporte publicado. Guarda este enlace para darle seguimiento; es el único acceso que tendrás a tu reporte.');
    }

    /**
     * Vista pública de un reporte (la que se comparte y aparece en el
     * listado). No muestra las acciones de gestión.
     */
    public function show(PetReport $report): View
    {
        // HU-20: oculto por denuncias mientras se revisa.
        abort_if($report->isHidden(), 404);

        return view('pet_reports.show', [
            'report' => $report,
            'canManage' => false,
            // HU-05: reportes del tipo contrario que se parecen.
            'similar' => $report->status === 'activo' ? $report->similarReports() : collect(),
        ]);
    }

    /**
     * Vista privada de gestión: solo quien tiene el enlace con el token
     * (entregado al publicar) puede marcar reunido o cerrar el caso.
     */
    public function manage(string $token): View
    {
        $report = PetReport::where('management_token', $token)->firstOrFail();

        return view('pet_reports.show', [
            'report' => $report,
            'canManage' => true,
            'similar' => $report->status === 'activo' ? $report->similarReports() : collect(),
        ]);
    }

    /**
     * Sprint 4 — HU-07: editar los datos del reporte con el enlace privado.
     * El tipo y la especie no cambian; un reporte cerrado hay que reabrirlo.
     */
    public function edit(string $token): View|RedirectResponse
    {
        $report = PetReport::where('management_token', $token)->firstOrFail();

        if ($report->status === 'cerrado') {
            return $this->closedRedirect($token);
        }

        return view('pet_reports.edit', [
            'report' => $report,
            'speciesInfo' => config("pets.species.{$report->species}"),
            'colors' => config('pets.colors'),
            'sizes' => config('pets.sizes'),
            'sexes' => config('pets.sexes'),
        ]);
    }

    public function update(UpdatePetReportRequest $request, string $token): RedirectResponse
    {
        $report = $request->managedReport();

        if ($report->status === 'cerrado') {
            return $this->closedRedirect($token);
        }

        $data = $request->reportData();

        if ($request->hasFile('photo')) {
            $report->deleteUploadedPhoto();
            $data['photo_path'] = $request->file('photo')->store('pet-reports', 'public');
        }

        $report->update($data);

        // HU-08: con los datos nuevos puede haber coincidencias nuevas.
        PetReportUpdated::dispatch($report);

        return redirect()->route('reports.manage', $token)->with('status', 'Guardamos los cambios de tu reporte.');
    }

    /**
     * HU-07: marcar reunido, cerrar o reabrir el reporte.
     */
    public function updateStatus(Request $request, string $token): RedirectResponse
    {
        $report = PetReport::where('management_token', $token)->firstOrFail();

        $request->validate([
            'status' => ['required', 'in:activo,reunido,cerrado'],
        ]);

        $report->update(['status' => $request->input('status')]);

        $message = match ($report->status) {
            'reunido' => '¡Qué alegría! Marcamos el reporte como reunido y ya no aparece en el listado.',
            'cerrado' => 'Cerraste el reporte: ya no aparece en el listado ni recibe contactos. Puedes reabrirlo cuando quieras.',
            default => 'Reabriste el reporte: vuelve a aparecer en el listado.',
        };

        if ($report->status === 'activo') {
            PetReportUpdated::dispatch($report);
        }

        return redirect()->route('reports.manage', $token)->with('status', $message);
    }

    private function closedRedirect(string $token): RedirectResponse
    {
        return redirect()->route('reports.manage', $token)
            ->with('status', 'Este reporte está cerrado. Reábrelo para poder editarlo.');
    }

    /**
     * Sprint 4 — HU-05: mientras se llena el formulario (antes de publicar)
     * se buscan reportes del tipo contrario que se parezcan.
     */
    public function similar(Request $request): JsonResponse
    {
        $bounds = config('pets.bounds');

        $data = $request->validate([
            'type' => ['required', Rule::in(['perdida', 'encontrada'])],
            'species' => ['required', Rule::in(array_keys(config('pets.species')))],
            'latitude' => ['required', 'numeric', 'between:'.$bounds['lat_min'].','.$bounds['lat_max']],
            'longitude' => ['required', 'numeric', 'between:'.$bounds['lng_min'].','.$bounds['lng_max']],
            'breed' => ['nullable', 'string', 'max:100'],
            'sex' => ['nullable', 'string', 'max:10'],
            'size' => ['nullable', 'string', 'max:20'],
            'colors' => ['nullable', 'array', 'max:'.config('pets.max_colors')],
            'colors.*' => ['string', 'max:30'],
            'event_date' => ['nullable', 'date'],
        ]);

        $draft = new PetReport(collect($data)->except('colors')->all());
        $draft->color = implode(',', $data['colors'] ?? []);
        if ($draft->breed === config('pets.unknown_breed')) {
            $draft->breed = null;
        }

        $reports = $draft->similarReports()->map(fn (PetReport $report) => [
            'id' => $report->id,
            'url' => $report->public_url,
            'name' => $report->display_name,
            'species' => $report->species_label,
            'emoji' => $report->species_emoji,
            'photo_url' => $report->photo_url,
            'location' => $report->location_reference,
            'distance_km' => round($report->distance_km, 1),
            'score' => $report->match_score,
            'reasons' => $report->match_reasons,
            'published' => $report->created_at->locale('es')->diffForHumans(),
        ]);

        return response()->json(['count' => $reports->count(), 'reports' => $reports]);
    }

    /** HU-10: copia la foto del perfil para que el reporte tenga la suya. */
    private function copyProfilePhoto(PetProfile $profile): string
    {
        if (Str::startsWith($profile->photo_path, 'images/')) {
            return $profile->photo_path;
        }

        $copy = 'pet-reports/'.Str::uuid().'.'.pathinfo($profile->photo_path, PATHINFO_EXTENSION);
        Storage::disk('public')->copy($profile->photo_path, $copy);

        return $copy;
    }

    /**
     * HU3: endpoint JSON para el "radar" de mascotas cercanas.
     * El frontend lo consulta con la ubicación del visitante (sin login)
     * para avisarle si hay casos activos a su alrededor.
     */
    public function nearby(Request $request): JsonResponse
    {
        $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'radius' => ['nullable', 'numeric', 'min:0.5', 'max:100'],
        ]);

        $reports = PetReport::near(
            PetReport::query()->active(),
            (float) $request->query('lat'),
            (float) $request->query('lng'),
            (float) $request->query('radius', 5)
        )->map(fn (PetReport $report) => collect($report->toArray())->only([
            'id', 'type', 'species', 'breed', 'color', 'size', 'pet_name', 'status',
            'description', 'location_reference', 'photo_path', 'latitude', 'longitude',
            'created_at', 'distance_km',
        ])->put('photo_url', $report->photo_url));

        return response()->json([
            'count' => $reports->count(),
            'reports' => $reports,
        ]);
    }
}
