<?php

declare(strict_types=1);

namespace App\Http\Requests\Setup;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class AppSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'app_name' => ['required', 'string', 'max:255'],
            'app_timezone' => ['required', 'string', Rule::in(\DateTimeZone::listIdentifiers())],
            'app_locale' => ['required', 'string', Rule::in(['it', 'en'])],
        ];
    }
}
