<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\VehicleType;
use App\Models\Company;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vehicle>
 */
final class VehicleFactory extends Factory
{
    public function definition(): array
    {
        $tipo = $this->faker->randomElement(VehicleType::cases());
        $numeroAssi = $this->faker->numberBetween(2, 5);

        return [
            'company_id' => Company::factory(),
            'tipo' => $tipo,
            'targa' => $this->generateTarga(),
            'numero_telaio' => $this->faker->optional()->bothify('???########??'),
            'marca' => $this->faker->randomElement(['Volvo', 'Scania', 'MAN', 'DAF', 'Mercedes', 'Iveco', 'Renault']),
            'modello' => $this->faker->bothify('FH?? ###'),
            'anno_immatricolazione' => $this->faker->numberBetween(2000, 2024),
            'massa_vuoto' => $this->faker->numberBetween(8000, 15000),
            'massa_complessiva' => $this->faker->numberBetween(16000, 44000),
            'lunghezza' => $this->faker->numberBetween(6000, 18750),
            'larghezza' => $this->faker->numberBetween(2400, 2550),
            'altezza' => $this->faker->numberBetween(3000, 4000),
            'numero_assi' => $numeroAssi,
        ];
    }

    /**
     * Genera una targa italiana nel formato AA000BB.
     */
    private function generateTarga(): string
    {
        $lettere = 'ABCDEFGHJKLMNPRSTUVWXYZ';
        $prefix = $this->faker->randomLetter().$this->faker->randomLetter();
        $numeri = $this->faker->numerify('###');
        $suffix = $this->faker->randomLetter().$this->faker->randomLetter();

        return strtoupper($prefix).strtoupper($numeri).strtoupper($suffix);
    }
}
