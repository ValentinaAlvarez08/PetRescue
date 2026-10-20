<?php

namespace App\Http\Controllers;

use App\Events\PetReportPublished;
use App\Http\Requests\StorePetReportRequest;
use App\Models\PetReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
     */
    public function create(string $type): View
    {
        abort_unless(in_array($type, ['perdida', 'encontrada'], true), 404);

        return view('pet_reports.create', [
            'type' => $type,
            'species' => config('pets.species'),
            'colors' => config('pets.colors'),
            'sizes' => config('pets.sizes'),
            'sexes' => config('pets.sexes'),
        ]);
    }

    public function store(StorePetReportRequest $request): RedirectResponse
    {
        $data = $request->reportData();

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('pet-reports', 'public');
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
        return view('pet_reports.show', [
            'report' => $report,
            'canManage' => false,
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
        ]);
    }

    public function updateStatus(Request $request, string $token): RedirectResponse
    {
        $report = PetReport::where('management_token', $token)->firstOrFail();

        $request->validate([
            'status' => ['required', 'in:activo,reunido,cerrado'],
        ]);

        $report->update(['status' => $request->input('status')]);

        return back()->with('status', 'Estado del reporte actualizado.');
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
