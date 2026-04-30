<?php

declare(strict_types=1);

namespace App\Http\Requests\Citizen;

use Illuminate\Foundation\Http\FormRequest;

final class StoreDelegationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'partita_iva' => ['required', 'string', 'regex:/^\d{11}$/'],
            'ragione_sociale' => ['required', 'string', 'max:255'],
            'valid_from' => ['required', 'date', 'after_or_equal:today'],
            'valid_to' => ['nullable', 'date', 'after:valid_from'],
            'codice_fiscale' => ['nullable', 'string', 'size:16'],
            'indirizzo' => ['nullable', 'string', 'max:255'],
            'comune' => ['nullable', 'string', 'max:100'],
            'cap' => ['nullable', 'string', 'size:5', 'regex:/^\d{5}$/'],
            'provincia' => ['nullable', 'string', 'size:2'],
            'email' => ['nullable', 'email', 'max:255'],
            'pec' => ['nullable', 'email', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:50'],
        ];
    }
}
