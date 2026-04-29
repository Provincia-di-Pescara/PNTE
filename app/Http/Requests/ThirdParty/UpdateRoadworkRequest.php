<?php

declare(strict_types=1);

namespace App\Http\Requests\ThirdParty;

use App\Enums\RoadworkSeverity;
use App\Enums\RoadworkStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateRoadworkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'entity_id' => ['required', 'integer', 'exists:entities,id'],
            'title' => ['required', 'string', 'max:255'],
            'geometry' => ['required', 'string', 'starts_with:LINESTRING,POLYGON', 'max:65535'],
            'valid_from' => ['required', 'date'],
            'valid_to' => ['nullable', 'date', 'after_or_equal:valid_from'],
            'severity' => ['required', Rule::enum(RoadworkSeverity::class)],
            'status' => ['required', Rule::enum(RoadworkStatus::class)],
            'note' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
