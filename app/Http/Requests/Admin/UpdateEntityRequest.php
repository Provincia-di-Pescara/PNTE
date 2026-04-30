<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\EntityType;
use App\Models\Entity;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateEntityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('entity'));
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        /** @var Entity $entity */
        $entity = $this->route('entity');

        return [
            'nome' => ['required', 'string', 'max:255'],
            'tipo' => ['required', Rule::enum(EntityType::class)],
            'codice_istat' => ['nullable', 'string', 'max:10', Rule::unique('entities', 'codice_istat')->ignore($entity->id)],
            'geom' => ['nullable', 'string'],
            'pec' => ['nullable', 'email', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'indirizzo' => ['nullable', 'string', 'max:255'],
            'codice_fisc_piva' => ['nullable', 'string', 'max:16'],
            'codice_sdi' => ['nullable', 'string', 'size:7'],
            'codice_univoco_ainop' => ['nullable', 'string', 'max:50'],
        ];
    }
}
