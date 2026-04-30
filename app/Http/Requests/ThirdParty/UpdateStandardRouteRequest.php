<?php

declare(strict_types=1);

namespace App\Http\Requests\ThirdParty;

use App\Enums\VehicleType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateStandardRouteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'entity_id' => ['required', 'integer', 'exists:entities,id'],
            'nome' => ['required', 'string', 'max:255'],
            'geometry' => ['required', 'string', 'starts_with:LINESTRING', 'max:65535'],
            'vehicle_types' => ['required', 'array', 'min:1'],
            'vehicle_types.*' => [Rule::enum(VehicleType::class)],
            'max_massa_kg' => ['nullable', 'integer', 'min:1'],
            'max_lunghezza_mm' => ['nullable', 'integer', 'min:1'],
            'max_larghezza_mm' => ['nullable', 'integer', 'min:1'],
            'max_altezza_mm' => ['nullable', 'integer', 'min:1'],
            'active' => ['boolean'],
            'note' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
