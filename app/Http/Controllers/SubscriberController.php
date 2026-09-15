<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSubscriberRequest;
use App\Models\Subscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SubscriberController extends Controller
{
    /**
     * HU3: formulario para activar avisos de reportes cercanos por correo.
     */
    public function create(): View
    {
        return view('subscribers.create');
    }

    public function store(StoreSubscriberRequest $request): RedirectResponse
    {
        $subscriber = Subscriber::create($request->validated());

        return redirect()
            ->route('reports.index')
            ->with('status', "Listo, {$subscriber->email}. Te avisaremos por correo cuando haya un reporte cerca de tu ubicación.");
    }

    /**
     * Baja sin login: el enlace de baja va incluido en cada notificación.
     */
    public function unsubscribe(string $token): RedirectResponse
    {
        $subscriber = Subscriber::where('unsubscribe_token', $token)->firstOrFail();

        // notifications_enabled no es mass-assignable a propósito (no debe
        // poder activarse/desactivarse desde el formulario de alta).
        $subscriber->notifications_enabled = false;
        $subscriber->save();

        return redirect()
            ->route('reports.index')
            ->with('status', 'Se desactivaron tus notificaciones de reportes cercanos.');
    }
}
