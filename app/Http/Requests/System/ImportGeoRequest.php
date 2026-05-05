<?php

declare(strict_types=1);

namespace App\Http\Requests\System;

use Illuminate\Foundation\Http\FormRequest;

final class ImportGeoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('system-admin') ?? false;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:json,geojson', 'max:51200'],
        ];
    }
}
