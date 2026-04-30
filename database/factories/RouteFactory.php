<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Route;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;

/**
 * @extends Factory<Route>
 */
final class RouteFactory extends Factory
{
    public function definition(): array
    {
        $lat1 = $this->faker->randomFloat(4, 41.8, 42.5);
        $lng1 = $this->faker->randomFloat(4, 13.0, 14.5);
        $lat2 = $lat1 + $this->faker->randomFloat(4, 0.01, 0.1);
        $lng2 = $lng1 + $this->faker->randomFloat(4, 0.01, 0.1);

        $wkt = "LINESTRING({$lng1} {$lat1}, {$lng2} {$lat2})";

        return [
            'user_id' => User::factory(),
            'waypoints' => [
                ['lat' => $lat1, 'lng' => $lng1],
                ['lat' => $lat2, 'lng' => $lng2],
            ],
            'geometry' => DB::raw("ST_GeomFromText('{$wkt}', 4326)"),
            'distance_km' => $this->faker->randomFloat(3, 1.0, 200.0),
            'entity_breakdown' => null,
        ];
    }
}
