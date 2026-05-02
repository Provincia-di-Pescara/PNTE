<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ClearanceStatus;
use App\Models\Application;
use App\Models\Clearance;
use App\Models\Entity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Clearance>
 */
final class ClearanceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'application_id' => Application::factory(),
            'entity_id' => Entity::factory(),
            'stato' => ClearanceStatus::Pending,
            'note' => null,
            'decided_at' => null,
            'decided_by' => null,
        ];
    }
}
