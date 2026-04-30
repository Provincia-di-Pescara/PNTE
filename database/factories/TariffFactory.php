<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AxleType;
use App\Enums\TipoApplicazioneTariff;
use App\Models\Tariff;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Tariff>
 */
final class TariffFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tipo_asse' => $this->faker->randomElement(AxleType::cases()),
            'tipo_applicazione' => TipoApplicazioneTariff::AnaliticaKm,
            'coefficiente' => $this->faker->randomFloat(6, 0.0001, 0.001),
            'valid_from' => Carbon::today()->subYear()->toDateString(),
            'valid_to' => null,
            'note' => $this->faker->optional()->sentence(),
        ];
    }
}
