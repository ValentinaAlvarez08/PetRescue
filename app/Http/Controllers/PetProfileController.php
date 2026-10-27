<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePetProfileRequest;
use App\Models\PetProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Sprint 4 — HU-10: perfil digital de la mascota.
 * Sin login: quien lo crea recibe un enlace privado para editarlo o
 * eliminarlo, y un enlace público (con QR para la placa del collar).
 */
class PetProfileController extends Controller
{
    public function create(): View
    {
        return view('profiles.create', $this->formData() + ['profile' => null]);
    }

    public function store(StorePetProfileRequest $request): RedirectResponse
    {
        $data = $request->profileData();
        $data['photo_path'] = $request->file('photo')->store('pet-profiles', 'public');

        $profile = PetProfile::create($data);

        return redirect()
            ->route('profiles.manage', $profile->management_token)
            ->with('status', "¡Listo! Creamos el perfil de {$profile->name}. Guarda este enlace privado: es la única forma de editarlo.");
    }

    /** CID 2: perfil público (el que abre quien escanea el QR). */
    public function show(PetProfile $profile): View
    {
        return view('profiles.show', [
            'profile' => $profile->load('activeLostReport'),
            'canManage' => false,
        ]);
    }

    /** CID 3: enlace privado para editar, imprimir la placa o reportar la pérdida. */
    public function manage(string $token): View
    {
        $profile = $this->profileByToken($token);

        return view('profiles.manage', $this->formData() + [
            'profile' => $profile->load('activeLostReport', 'reports'),
        ]);
    }

    public function update(StorePetProfileRequest $request, string $token): RedirectResponse
    {
        $profile = $this->profileByToken($token);
        $data = $request->profileData();

        if ($request->hasFile('photo')) {
            $profile->deleteUploadedPhoto();
            $data['photo_path'] = $request->file('photo')->store('pet-profiles', 'public');
        }

        $profile->update($data);

        return redirect()->route('profiles.manage', $token)->with('status', 'Guardamos los cambios del perfil.');
    }

    public function destroy(string $token): RedirectResponse
    {
        $profile = $this->profileByToken($token);
        $name = $profile->name;

        // Los reportes creados desde el perfil se conservan (pet_profile_id queda en null).
        $profile->deleteUploadedPhoto();
        $profile->delete();

        return redirect()->route('profiles.create')->with('status', "Eliminamos el perfil de {$name}.");
    }

    private function profileByToken(string $token): PetProfile
    {
        return PetProfile::where('management_token', $token)->firstOrFail();
    }

    private function formData(): array
    {
        return [
            'species' => config('pets.species'),
            'colors' => config('pets.colors'),
            'sizes' => config('pets.sizes'),
            'sexes' => array_diff_key(config('pets.sexes'), ['no_se' => true]),
            'sterilizedOptions' => config('pets.sterilized'),
        ];
    }
}
