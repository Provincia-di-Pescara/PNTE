<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\RoadworkSeverity;
use App\Enums\RoadworkStatus;
use App\Models\Entity;
use App\Models\Roadwork;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

/**
 * @extends Factory<Roadwork>
 */
final class RoadworkFactory extends Factory
{
    public function definition(): array
    {
        $lat1 = $this->faker->randomFloat(4, 41.8, 42.5);
        $lng1 = $this->faker->randomFloat(4, 13.0, 14.5);
        $lat2 = $lat1 + $this->faker->randomFloat(4, 0.01, 0.05);
        $lng2 = $lng1 + $this->faker->randomFloat(4, 0.01, 0.05);

        $wkt = "LINESTRING({$lng1} {$lat1}, {$lng2} {$lat2})";

        return [
            'entity_id' => Entity::factory(),
            'title' => 'Cantiere '.$this->faker->streetName(),
            'geometry' => DB::raw("ST_GeomFromText('{$wkt}', 4326)"),
            'valid_from' => $this->faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'valid_to' => $this->faker->optional()->dateTimeBetween('now', '+6 months')?->format('Y-m-d'),
            'severity' => $this->faker->randomElement(RoadworkSeverity::cases())->value,
            'status' => $this->faker->randomElement(RoadworkStatus::cases())->value,
            'note' => $this->faker->optional()->sentence(),
        ];
    }
}
