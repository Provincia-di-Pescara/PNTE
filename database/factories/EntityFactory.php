<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\EntityType;
use App\Models\Entity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Entity>
 */
final class EntityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nome' => 'Comune di '.$this->faker->city(),
            'tipo' => EntityType::Comune,
            'codice_istat' => $this->faker->unique()->numerify('######'),
            'geom' => null,
            'pec' => $this->faker->unique()->safeEmail(),
            'email' => $this->faker->safeEmail(),
            'telefono' => $this->faker->phoneNumber(),
            'indirizzo' => $this->faker->streetAddress(),
            'codice_fisc_piva' => null,
            'codice_sdi' => null,
            'codice_univoco_ainop' => null,
        ];
    }
}
