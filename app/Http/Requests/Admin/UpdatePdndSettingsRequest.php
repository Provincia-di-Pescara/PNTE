<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

final class UpdatePdndSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // SettingController::denyUnlessSuper() handles authorization
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'pdnd_enabled' => ['nullable', 'boolean'],
            'pdnd_client_id' => ['nullable', 'string', 'max:255'],
            'pdnd_token_endpoint' => ['nullable', 'url', 'max:500'],
            'pdnd_private_key' => ['nullable', 'string'],
            'pdnd_dpop_private_key' => ['nullable', 'string'],
            'pdnd_ipa_url' => ['nullable', 'url', 'max:500'],
            'pdnd_infocamere_url' => ['nullable', 'url', 'max:500'],
        ];
    }
}
