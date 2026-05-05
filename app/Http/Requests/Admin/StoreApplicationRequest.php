<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Enums\TipoIstanza;
use App\Models\Vehicle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->companies()
            ->whereNotNull('company_user.approved_at')
            ->exists() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $tipoIstanza = TipoIstanza::tryFrom((string) $this->input('tipo_istanza'));

        $rules = [
            'tipo_istanza' => ['required', Rule::enum(TipoIstanza::class)],
            'company_id' => ['required', 'exists:companies,id'],
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'trailer_id' => ['nullable', 'exists:vehicles,id'],
            'valida_da' => ['required', 'date', 'after_or_equal:today'],
            'valida_fino' => ['required', 'date', 'after:valida_da'],
            'note' => ['nullable', 'string', 'max:2000'],
        ];

        if ($tipoIstanza === TipoIstanza::AnaliticaKm) {
            $rules['route_id'] = ['required', 'exists:routes,id'];
        }

        if ($tipoIstanza === TipoIstanza::ForfettariaPeriodica) {
            $rules['route_id'] = ['required', 'exists:routes,id'];
            $rules['numero_viaggi'] = ['required', 'integer', 'min:1'];
            $rules['selected_entity_ids'] = ['required', 'array', 'min:1'];
            $rules['selected_entity_ids.*'] = ['integer', 'exists:entities,id'];
        }

        if ($tipoIstanza === TipoIstanza::ForfettariaAgricola) {
            $rules['route_id'] = ['nullable', 'exists:routes,id'];
            $rules['vehicle_id'] = [
                'required',
                'exists:vehicles,id',
                function (string $attribute, mixed $value, callable $fail): void {
                    $vehicle = Vehicle::find($value);
                    if ($vehicle && ! $vehicle->tipo->isAgricultural()) {
                        $fail('Il veicolo deve essere di tipo agricolo per questa tipologia di istanza.');
                    }
                },
            ];
        }

        return $rules;
    }
}
