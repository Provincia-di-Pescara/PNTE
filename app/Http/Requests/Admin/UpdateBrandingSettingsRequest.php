<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateBrandingSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole(UserRole::SuperAdmin->value);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'brand_header_title' => ['required', 'string', 'max:100'],
            'brand_primary_color' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'brand_logo' => ['nullable', 'image', 'mimes:png,svg,jpg,webp', 'max:512'],
        ];
    }
}
