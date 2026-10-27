<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Notifications\BusinessRequestStatusNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

/**
 * Sprint 4 — HU-19 / HU-20: revisión de moderación sin login de
 * administrador. Se entra solo con los enlaces firmados que llegan al
 * correo de moderación (middleware "signed"). La página GET solo muestra;
 * la acción se confirma con un botón (POST), así un antivirus que abra el
 * enlace del correo no aprueba ni restaura nada por accidente.
 */
class ModerationController extends Controller
{
    /** HU-19: revisar la solicitud de un negocio. */
    public function showBusiness(Request $request, Business $business): View
    {
        return view('moderation.business', [
            'business' => $business,
            'action' => $request->fullUrl(),
        ]);
    }

    public function reviewBusiness(Request $request, Business $business): RedirectResponse
    {
        $data = $request->validate([
            'decision' => ['required', 'in:aprobar,rechazar'],
            'note' => ['nullable', 'string', 'max:300'],
        ]);

        if ($business->review_status !== 'pendiente') {
            return redirect()->to($request->fullUrl())->with('status', 'Esta solicitud ya había sido revisada.');
        }

        $approved = $data['decision'] === 'aprobar';
        $business->review($approved, $approved ? null : ($data['note'] ?? null));

        if ($business->requester_email) {
            Notification::route('mail', $business->requester_email)
                ->notify(new BusinessRequestStatusNotification($business));
        }

        return redirect()->to($request->fullUrl())->with('status', $approved
            ? "Aprobaste «{$business->name}»: ya aparece en el directorio."
            : "Rechazaste «{$business->name}». Le avisamos por correo a quien lo solicitó.");
    }

    /** HU-20: revisar contenido oculto por denuncias. */
    public function showFlagged(Request $request, string $type, int $id): View
    {
        $content = $this->flaggedContent($type, $id);

        return view('moderation.flagged', [
            'content' => $content,
            'type' => $type,
            'flags' => $content->flags()->latest()->get(),
            'action' => $request->fullUrl(),
        ]);
    }

    public function restoreFlagged(Request $request, string $type, int $id): RedirectResponse
    {
        $content = $this->flaggedContent($type, $id);

        if (! $content->isHidden()) {
            return redirect()->to($request->fullUrl())->with('status', 'Este contenido ya estaba visible.');
        }

        $content->restoreFromFlags();

        return redirect()->to($request->fullUrl())->with('status', 'Listo: el contenido vuelve a estar visible y se borraron sus denuncias.');
    }

    private function flaggedContent(string $type, int $id): Model
    {
        $class = Relation::getMorphedModel($type);
        abort_unless($class, 404);

        return $class::findOrFail($id);
    }
}
