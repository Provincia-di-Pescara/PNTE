<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Company>
 */
final class CompanyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'ragione_sociale' => $this->faker->company().' S.r.l.',
            'partita_iva' => $this->faker->unique()->numerify('###########'),
            'codice_fiscale' => null,
            'indirizzo' => $this->faker->streetAddress(),
            'comune' => $this->faker->city(),
            'cap' => $this->faker->numerify('#####'),
            'provincia' => strtoupper($this->faker->lexify('??')),
            'email' => $this->faker->companyEmail(),
            'pec' => null,
            'telefono' => $this->faker->phoneNumber(),
        ];
    }
}
