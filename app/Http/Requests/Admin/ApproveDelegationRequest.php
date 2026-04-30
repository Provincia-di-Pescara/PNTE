<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ApproveDelegationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('approveDelegation', $this->route('company'));
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'action' => ['required', Rule::in(['approve', 'reject'])],
        ];
    }
}
