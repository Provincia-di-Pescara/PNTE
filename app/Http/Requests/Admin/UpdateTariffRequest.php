<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\AxleType;
use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateTariffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole([UserRole::SuperAdmin->value, UserRole::Operator->value]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'tipo_asse' => ['required', Rule::enum(AxleType::class)],
            'coefficiente' => ['required', 'numeric', 'min:0', 'decimal:0,6'],
            'valid_from' => ['required', 'date'],
            'valid_to' => ['nullable', 'date', 'after:valid_from'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
