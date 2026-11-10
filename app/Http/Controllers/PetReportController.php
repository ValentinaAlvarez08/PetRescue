<?php

namespace App\Http\Controllers;

use App\Contracts\PhotoStorage;
use App\Contracts\ReportMatcher;
use App\Events\PetReportPublished;
use App\Events\PetReportUpdated;
use App\Http\Filters\ReportFilters;
use App\Http\Requests\StorePetReportRequest;
use App\Http\Requests\UpdatePetReportRequest;
use App\Models\PetProfile;
use App\Models\PetReport;
use App\Services\Geo\NearbyFinder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Páginas de los reportes: listado, publicar, ver y gestionar con el enlace
 * privado. Los endpoints JSON están en Api\PetReportApiController.
 */
class PetReportController extends Controller
{
    private const PHOTO_FOLDER = 'pet-reports';

    public function __construct(
        private readonly PhotoStorage $photos,
        private readonly ReportMatcher $matcher,
        private readonly NearbyFinder $nearby,
    ) {
    }

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
        $filters = ReportFilters::fromRequest($request);
        $query = $filters->apply(PetReport::query()->active());

        $reports = $filters->hasLocation()
            ? $this->nearby->within($query, $filters->lat, $filters->lng, $filters->radius)
            : $query->latest()->get();

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
            'lat' => $filters->lat,
            'lng' => $filters->lng,
            'radius' => $filters->radius,
            'type' => $filters->type,
            'filters' => $filters->toArray(),
            'hasFilters' => $filters->isFiltering(),
            'speciesCatalog' => config('pets.species'),
            'colorsCatalog' => config('pets.colors'),
            'radiusOptions' => ReportFilters::RADIUS_OPTIONS,
        ]);
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
            $data['photo_path'] = $this->photos->store($request->file('photo'), self::PHOTO_FOLDER);
        } elseif ($profile = $request->profile()) {
            // HU-10: el reporte tiene su propia copia de la foto del perfil.
            $data['photo_path'] = $this->photos->copy($profile->photo_path, self::PHOTO_FOLDER);
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
            'similar' => $this->similarTo($report),
        ]);
    }

    /**
     * Vista privada de gestión: solo quien tiene el enlace con el token
     * (entregado al publicar) puede marcar reunido o cerrar el caso.
     */
    public function manage(string $token): View
    {
        $report = $this->reportByToken($token);

        return view('pet_reports.show', [
            'report' => $report,
            'canManage' => true,
            'similar' => $this->similarTo($report),
        ]);
    }

    /**
     * Sprint 4 — HU-07: editar los datos del reporte con el enlace privado.
     * El tipo y la especie no cambian; un reporte cerrado hay que reabrirlo.
     */
    public function edit(string $token): View|RedirectResponse
    {
        $report = $this->reportByToken($token);

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
            $this->photos->delete($report->photo_path);
            $data['photo_path'] = $this->photos->store($request->file('photo'), self::PHOTO_FOLDER);
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
        $report = $this->reportByToken($token);

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

    private function reportByToken(string $token): PetReport
    {
        return PetReport::where('management_token', $token)->firstOrFail();
    }

    /** HU-05: reportes del tipo contrario que se parecen (solo si sigue activo). */
    private function similarTo(PetReport $report): Collection
    {
        return $report->status === 'activo' ? $this->matcher->for($report) : collect();
    }
}
