<?php

namespace App\Http\Controllers;

use App\Events\PetReportPublished;
use App\Http\Requests\StorePetReportRequest;
use App\Models\PetReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PetReportController extends Controller
{
    /**
     * Listado público. Si llegan lat/lng, se muestran ordenados por
     * cercanía (HU3). Sin esos parámetros, se listan los más recientes.
     */
    public function index(Request $request): View
    {
        $lat = $request->query('lat');
        $lng = $request->query('lng');
        $radius = (float) $request->query('radius', 5);

        $query = PetReport::query()->active();

        if ($request->filled('type')) {
            $query->ofType($request->query('type'));
        }

        if ($lat !== null && $lng !== null) {
            $reports = PetReport::near($query, (float) $lat, (float) $lng, $radius);
        } else {
            $reports = $query->latest()->get();
        }

        return view('pet_reports.index', [
            'reports' => $reports,
            'lat' => $lat,
            'lng' => $lng,
            'radius' => $radius,
        ]);
    }

    /**
     * HU1 / HU2: formulario de reporte (perdida o encontrada), sin login.
     */
    public function create(string $type): View
    {
        abort_unless(in_array($type, ['perdida', 'encontrada'], true), 404);

        return view('pet_reports.create', ['type' => $type]);
    }

    public function store(StorePetReportRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('pet-reports', 'public');
        }

        $report = PetReport::create($data);

        // HU3: avisa automáticamente a los suscriptores cercanos.
        PetReportPublished::dispatch($report);

        // El "enlace de gestión" reemplaza el login: es lo único que
        // necesita el usuario para editar o cerrar su propio caso.
        return redirect()
            ->route('reports.show', $report->management_token)
            ->with('status', 'Reporte publicado. Guarda este enlace para darle seguimiento; es el único acceso que tendrás a tu reporte.');
    }

    /**
     * Vista de un reporte. Si coincide el token en la URL con el guardado
     * en BD, se muestran también las acciones de gestión (marcar reunido,
     * cerrar caso) — así no se requiere sesión ni cuenta.
     */
    public function show(string $token): View
    {
        $report = PetReport::where('management_token', $token)->firstOrFail();

        return view('pet_reports.show', [
            'report' => $report,
            'canManage' => true, // quien tiene el enlace es, por definición, el dueño del reporte
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
            'id', 'type', 'pet_name', 'description', 'location_reference', 'photo_path', 'created_at', 'distance_km',
        ]));

        return response()->json([
            'count' => $reports->count(),
            'reports' => $reports,
        ]);
    }
}
