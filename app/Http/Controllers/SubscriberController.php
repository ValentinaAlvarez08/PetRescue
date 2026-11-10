<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAlertZoneRequest;
use App\Http\Requests\StoreSubscriberRequest;
use App\Models\AlertZone;
use App\Models\Subscriber;
use App\Notifications\AlertSubscriptionConfirmedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SubscriberController extends Controller
{
    /**
     * HU3 / HU-11: formulario para activar avisos de una zona por correo.
     */
    public function create(): View
    {
        return view('subscribers.create', [
            'alerts' => config('pets.alerts'),
        ]);
    }

    /**
     * HU-11 (CID 1): guarda la zona. Si el correo ya estaba suscrito, la
     * zona se suma a las que ya tiene (un solo suscriptor por correo, para
     * no repetir avisos del mismo reporte).
     *
     * El enlace de administración se envía solo al correo: así nadie puede
     * ver las zonas (casa, trabajo...) de otra persona escribiendo su correo.
     */
    public function store(StoreSubscriberRequest $request): RedirectResponse
    {
        $subscriber = Subscriber::firstOrCreate(
            ['email' => Str::lower(trim($request->input('email')))],
            ['name' => $request->input('name')]
        );

        if (! $subscriber->name && $request->filled('name')) {
            $subscriber->update(['name' => $request->input('name')]);
        }

        $zone = $this->saveZone($subscriber, $request->zoneData());

        if (! $zone) {
            return back()->withInput()->withErrors(['label' => $this->maxZonesMessage()]);
        }

        $subscriber->notify(new AlertSubscriptionConfirmedNotification($zone));

        return redirect()
            ->route('subscribers.create')
            ->withInput($request->only('name', 'email'))
            ->with('status', "¡Listo! Guardamos la zona «{$zone->label}» con un radio de {$zone->radius_label} km. "
                ."Te escribimos a {$subscriber->email} cuando haya un reporte ahí; en ese correo está el enlace para administrar todas tus zonas.");
    }

    /**
     * HU-11: administración de todas las zonas desde el enlace privado.
     */
    public function manage(string $token): View
    {
        $subscriber = $this->subscriberByToken($token);

        return view('subscribers.manage', [
            'subscriber' => $subscriber,
            'zones' => $subscriber->zones,
            'alerts' => config('pets.alerts'),
        ]);
    }

    public function storeZone(StoreAlertZoneRequest $request, string $token): RedirectResponse
    {
        $subscriber = $this->subscriberByToken($token);
        $zone = $this->saveZone($subscriber, $request->zoneData());

        if (! $zone) {
            return back()->withInput()->withErrors(['label' => $this->maxZonesMessage()]);
        }

        return redirect()
            ->route('subscribers.manage', $token)
            ->with('status', "Zona «{$zone->label}» guardada.");
    }

    public function updateZone(Request $request, string $token, int $zone): RedirectResponse
    {
        $zone = $this->zoneOf($this->subscriberByToken($token), $zone);
        $alerts = config('pets.alerts');

        $data = $request->validate([
            'label' => ['sometimes', 'required', 'string', 'max:40'],
            'radius_km' => ['sometimes', 'required', 'integer', 'between:'.$alerts['radius_min'].','.$alerts['radius_max']],
            'active' => ['sometimes', 'boolean'],
        ], [
            'label.required' => 'La zona necesita un nombre.',
            'radius_km.between' => "El radio debe ser de {$alerts['radius_min']} a {$alerts['radius_max']} km.",
        ]);

        if (array_key_exists('label', $data)) {
            $data['label'] = trim($data['label']);
        }

        $zone->fill($data);
        if (array_key_exists('active', $data)) {
            $zone->active = (bool) $data['active'];
        }
        $zone->save();

        $message = match (true) {
            ! array_key_exists('active', $data) => "Zona «{$zone->label}» actualizada.",
            $zone->active => "Se activaron las alertas de la zona «{$zone->label}».",
            default => "Se pausaron las alertas de la zona «{$zone->label}».",
        };

        return redirect()->route('subscribers.manage', $token)->with('status', $message);
    }

    public function destroyZone(string $token, int $zone): RedirectResponse
    {
        $zone = $this->zoneOf($this->subscriberByToken($token), $zone);
        $zone->delete();

        return redirect()
            ->route('subscribers.manage', $token)
            ->with('status', "Se eliminó la zona «{$zone->label}».");
    }

    /**
     * HU-11 (CID 4): baja sin login desde el enlace del correo. Solo se
     * desactiva la zona que generó ese correo; las demás siguen activas.
     */
    public function unsubscribe(string $token): RedirectResponse
    {
        $zone = AlertZone::with('subscriber')->where('unsubscribe_token', $token)->firstOrFail();

        // "active" no es mass-assignable a propósito (no debe poder
        // cambiarse desde el formulario de alta).
        $zone->active = false;
        $zone->save();

        return redirect()
            ->route('subscribers.manage', $zone->subscriber->manage_token)
            ->with('status', "Se desactivaron las alertas de la zona «{$zone->label}». Tus demás zonas siguen igual.");
    }

    /**
     * Una zona con el mismo nombre se actualiza y se reactiva (ej. volver a
     * suscribir "Casa" después de darse de baja). Devuelve null si ya se
     * alcanzó el máximo de zonas por correo.
     */
    private function saveZone(Subscriber $subscriber, array $data): ?AlertZone
    {
        $zone = $subscriber->zones()->get()
            ->first(fn (AlertZone $z) => Str::lower($z->label) === Str::lower($data['label']));

        if (! $zone) {
            if ($subscriber->zones()->count() >= config('pets.alerts.max_zones')) {
                return null;
            }
            $zone = $subscriber->zones()->make();
        }

        $zone->fill($data);
        $zone->active = true;
        $zone->save();

        return $zone;
    }

    private function maxZonesMessage(): string
    {
        $max = config('pets.alerts.max_zones');

        return "Puedes vigilar hasta {$max} zonas por correo. Elimina una desde el enlace de administración que te enviamos.";
    }

    private function subscriberByToken(string $token): Subscriber
    {
        return Subscriber::where('manage_token', $token)->firstOrFail();
    }

    private function zoneOf(Subscriber $subscriber, int $zoneId): AlertZone
    {
        return $subscriber->zones()->whereKey($zoneId)->firstOrFail();
    }
}
