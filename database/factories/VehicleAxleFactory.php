<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AxleType;
use App\Models\Vehicle;
use App\Models\VehicleAxle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VehicleAxle>
 */
final class VehicleAxleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vehicle_id' => Vehicle::factory(),
            'posizione' => 1,
            'tipo' => $this->faker->randomElement(AxleType::cases()),
            'interasse' => $this->faker->optional()->numberBetween(1200, 6000),
            'carico_tecnico' => $this->faker->numberBetween(5000, 12000),
        ];
    }
}
