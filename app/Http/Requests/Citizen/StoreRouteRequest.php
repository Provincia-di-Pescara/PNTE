<?php

declare(strict_types=1);

namespace App\Http\Requests\Citizen;

use Illuminate\Foundation\Http\FormRequest;

final class StoreRouteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'waypoints' => ['required', 'json'],
            'geometry' => ['required', 'string', 'starts_with:LINESTRING', 'max:65535'],
            'distance_km' => ['required', 'numeric', 'min:0'],
        ];
    }
}
