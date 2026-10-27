<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBusinessRequest;
use App\Models\Business;
use App\Notifications\BusinessRequestedNotification;
use App\Notifications\BusinessRequestStatusNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

/**
 * Sprint 2 — HU-16: directorio de servicios para mascotas con mapa.
 * Sprint 4 — HU-19: los negocios pueden pedir que los agreguen.
 */
class BusinessController extends Controller
{
    public function index(Request $request): View
    {
        $categories = config('community.directory');
        $category = $request->query('categoria');

        if ($category !== null && ! array_key_exists($category, $categories)) {
            $category = null;
        }

        $businesses = Business::active()->orderBy('name')->get();

        return view('directory.index', [
            'categories' => $categories,
            'selected' => $category,
            'businesses' => $businesses,
            'mapBusinesses' => $businesses->map->toMapArray()->values(),
            'center' => config('pets.default_center'),
        ]);
    }

    /** HU-19: formulario "Registrar mi negocio". */
    public function create(): View
    {
        return view('directory.create', [
            'categories' => config('community.directory'),
        ]);
    }

    /**
     * HU-19: la solicitud queda pendiente (no aparece en el directorio) hasta
     * que moderación la apruebe desde el enlace que le llega por correo.
     */
    public function store(StoreBusinessRequest $request): RedirectResponse
    {
        $business = Business::create($request->businessData());

        Notification::route('mail', $business->requester_email)
            ->notify(new BusinessRequestStatusNotification($business));

        if ($moderator = config('community.moderation.email')) {
            Notification::route('mail', $moderator)->notify(new BusinessRequestedNotification($business));
        }

        return redirect()
            ->route('directory.index')
            ->with('status', "Recibimos la solicitud de «{$business->name}». La revisaremos y te escribiremos a {$business->requester_email} cuando aparezca en el directorio.");
    }
}
