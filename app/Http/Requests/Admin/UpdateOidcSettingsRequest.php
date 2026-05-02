<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateOidcSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'oidc_enabled' => ['nullable', 'boolean'],
            'oidc_discovery_url' => ['nullable', 'url', 'max:500'],
            'oidc_client_id' => ['nullable', 'string', 'max:255'],
            'oidc_client_secret' => ['nullable', 'string', 'max:500'],
            'oidc_scopes' => ['nullable', 'string', 'max:255'],
        ];
    }
}
