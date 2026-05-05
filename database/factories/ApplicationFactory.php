<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ApplicationStatus;
use App\Enums\TipoIstanza;
use App\Models\Application;
use App\Models\Company;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Application>
 */
final class ApplicationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'company_id' => Company::factory(),
            'vehicle_id' => Vehicle::factory(),
            'stato' => ApplicationStatus::Draft,
            'tipo_istanza' => TipoIstanza::AnaliticaKm,
            'valida_da' => now()->addDay(),
            'valida_fino' => now()->addMonth(),
        ];
    }

    public function draft(): static
    {
        return $this->state(['stato' => ApplicationStatus::Draft]);
    }

    public function submitted(): static
    {
        return $this->state(['stato' => ApplicationStatus::Submitted]);
    }

    public function approved(): static
    {
        return $this->state(['stato' => ApplicationStatus::Approved]);
    }
}
