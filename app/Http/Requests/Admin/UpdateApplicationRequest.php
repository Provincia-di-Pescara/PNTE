<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->companies()
            ->whereNotNull('company_user.approved_at')
            ->exists() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'company_id' => ['required', 'exists:companies,id'],
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'trailer_id' => ['nullable', 'exists:vehicles,id'],
            'valida_da' => ['required', 'date'],
            'valida_fino' => ['required', 'date', 'after:valida_da'],
            'numero_viaggi' => ['nullable', 'integer', 'min:1'],
            'note' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
