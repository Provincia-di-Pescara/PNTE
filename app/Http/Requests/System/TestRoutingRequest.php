<?php

declare(strict_types=1);

namespace App\Http\Requests\System;

use Illuminate\Foundation\Http\FormRequest;

final class TestRoutingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>|string>
     */
    public function rules(): array
    {
        return [
            'from' => ['required', 'array'],
            'from.lat' => ['required', 'numeric', 'between:-90,90'],
            'from.lng' => ['required', 'numeric', 'between:-180,180'],
            'to' => ['required', 'array'],
            'to.lat' => ['required', 'numeric', 'between:-90,90'],
            'to.lng' => ['required', 'numeric', 'between:-180,180'],
        ];
    }
}
