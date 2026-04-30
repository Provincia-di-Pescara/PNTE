<?php

declare(strict_types=1);

namespace App\Http\Requests\Citizen;

use App\Enums\AxleType;
use App\Enums\VehicleType;
use App\Models\Vehicle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Vehicle $vehicle */
        $vehicle = $this->route('vehicle');

        if (! $vehicle->relationLoaded('company')) {
            $vehicle->load('company');
        }

        return $this->user()->companies()
            ->where('company_id', $vehicle->company->id)
            ->whereNotNull('approved_at')
            ->exists();
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        /** @var Vehicle $vehicle */
        $vehicle = $this->route('vehicle');

        return [
            'tipo'                   => ['required', Rule::enum(VehicleType::class)],
            'targa'                  => ['required', 'string', 'max:15', 'unique:vehicles,targa,'.$vehicle->id],
            'numero_telaio'          => ['nullable', 'string', 'max:17'],
            'marca'                  => ['nullable', 'string', 'max:100'],
            'modello'                => ['nullable', 'string', 'max:100'],
            'anno_immatricolazione'  => ['nullable', 'integer', 'min:1900', 'max:'.date('Y')],
            'massa_vuoto'            => ['nullable', 'integer', 'min:0'],
            'massa_complessiva'      => ['nullable', 'integer', 'min:0'],
            'lunghezza'              => ['nullable', 'integer', 'min:0'],
            'larghezza'              => ['nullable', 'integer', 'min:0'],
            'altezza'                => ['nullable', 'integer', 'min:0'],
            'axles'                  => ['required', 'array', 'min:1', 'max:9'],
            'axles.*.posizione'      => ['required', 'integer', 'min:1'],
            'axles.*.tipo'           => ['required', Rule::enum(AxleType::class)],
            'axles.*.interasse'      => ['nullable', 'integer', 'min:0'],
            'axles.*.carico_tecnico' => ['required', 'integer', 'min:1'],
        ];
    }
}
