<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Company;
use Illuminate\Foundation\Http\FormRequest;

final class StoreCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Company::class);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'ragione_sociale' => ['required', 'string', 'max:255'],
            'partita_iva' => ['required', 'string', 'size:11', 'unique:companies,partita_iva', 'regex:/^\d{11}$/'],
            'codice_fiscale' => ['nullable', 'string', 'size:16', 'unique:companies,codice_fiscale'],
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
