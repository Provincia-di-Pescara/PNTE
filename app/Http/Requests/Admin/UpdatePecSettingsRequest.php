<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

final class UpdatePecSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'pec_host' => ['required', 'string', 'max:255'],
            'pec_port' => ['required', 'integer', 'min:1', 'max:65535'],
            'pec_username' => ['required', 'email', 'max:255'],
            'pec_password' => ['nullable', 'string', 'max:500'],
            'pec_encryption' => ['required', 'in:ssl,tls,none'],
            'pec_smtp_host' => ['nullable', 'string', 'max:255'],
            'pec_smtp_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
        ];
    }
}
