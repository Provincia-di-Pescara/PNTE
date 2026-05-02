<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Company;
use Illuminate\Foundation\Http\FormRequest;

final class LookupCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Company::class) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'piva' => ['required', 'string', 'regex:/^\d{11}$/'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'piva.required' => 'La Partita IVA è obbligatoria.',
            'piva.regex' => 'La Partita IVA deve essere composta da 11 cifre.',
        ];
    }
}
