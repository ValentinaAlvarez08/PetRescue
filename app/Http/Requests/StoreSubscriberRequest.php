<?php

namespace App\Http\Requests;

/**
 * HU3 / HU-11 (CID 1): alta de avisos desde el formulario público: el
 * correo de la persona más la primera (o una nueva) zona a vigilar.
 */
class StoreSubscriberRequest extends StoreAlertZoneRequest
{
    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
        ] + parent::rules();
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Necesitamos tu correo para poder avisarte.',
            'email.email' => 'Revisa tu correo, parece que no es válido.',
        ] + parent::messages();
    }
}
