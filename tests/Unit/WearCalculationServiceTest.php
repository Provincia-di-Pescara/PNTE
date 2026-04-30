<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\AxleType;
use App\Enums\UserRole;
use App\Models\Company;
use App\Models\Setting;
use App\Models\Tariff;
use App\Models\Vehicle;
use App\Models\VehicleAxle;
use App\Services\WearCalculationService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class WearCalculationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (UserRole::cases() as $role) {
            Role::firstOrCreate(['name' => $role->value, 'guard_name' => 'web']);
        }
        Setting::set('setup_completed', '1');
    }

    public function test_calculates_single_axle_correctly(): void
    {
        Tariff::factory()->create([
            'tipo_asse' => 'singolo',
            'coefficiente' => '0.001000',
            'valid_from' => today()->subDay(),
            'valid_to' => null,
        ]);

        $service = new WearCalculationService;
        $result = $service->calculate(
            [['tipo' => 'singolo', 'carico_tecnico' => 10000]],
            100.0
        );

        // (10000/1000)^4 × 100 × 0.001 = 10^4 × 100 × 0.001 = 10000 × 0.1 = 1000.0
        $this->assertEqualsWithDelta(1000.0, $result, 0.000001);
    }

    public function test_calculates_multiple_axles_summing_contributions(): void
    {
        Tariff::factory()->create([
            'tipo_asse' => 'singolo',
            'coefficiente' => '0.001000',
            'valid_from' => today()->subDay(),
            'valid_to' => null,
        ]);
        Tariff::factory()->create([
            'tipo_asse' => 'tandem',
            'coefficiente' => '0.002000',
            'valid_from' => today()->subDay(),
            'valid_to' => null,
        ]);

        $service = new WearCalculationService;
        $result = $service->calculate(
            [
                ['tipo' => 'singolo', 'carico_tecnico' => 10000],
                ['tipo' => 'tandem',  'carico_tecnico' => 5000],
            ],
            100.0
        );

        // Singolo: (10000/1000)^4 × 100 × 0.001 = 10000 × 0.1 = 1000.0
        // Tandem:  (5000/1000)^4  × 100 × 0.002 = 625  × 0.2  = 125.0
        // Total = 1125.0
        $this->assertEqualsWithDelta(1125.0, $result, 0.000001);
    }

    public function test_throws_when_no_active_tariff_found(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $service = new WearCalculationService;
        $service->calculate(
            [['tipo' => 'singolo', 'carico_tecnico' => 10000]],
            100.0
        );
    }

    public function test_distance_scales_linearly(): void
    {
        Tariff::factory()->create([
            'tipo_asse' => 'singolo',
            'coefficiente' => '0.001000',
            'valid_from' => today()->subDay(),
            'valid_to' => null,
        ]);

        $service = new WearCalculationService;
        $axles = [['tipo' => 'singolo', 'carico_tecnico' => 10000]];

        $result100 = $service->calculate($axles, 100.0);
        $result200 = $service->calculate($axles, 200.0);

        $this->assertEqualsWithDelta($result100 * 2, $result200, 0.000001);
    }

    public function test_calculate_for_vehicle_uses_technical_load(): void
    {
        $company = Company::factory()->create();
        $vehicle = Vehicle::factory()->for($company)->create(['numero_assi' => 1]);
        VehicleAxle::factory()->for($vehicle)->create([
            'tipo' => AxleType::Singolo,
            'carico_tecnico' => 8000,
            'posizione' => 1,
        ]);

        Tariff::factory()->create([
            'tipo_asse' => 'singolo',
            'coefficiente' => '0.002000',
            'valid_from' => today()->subDay(),
            'valid_to' => null,
        ]);

        $service = new WearCalculationService;
        $result = $service->calculateForVehicle($vehicle, 50.0);

        // (8000/1000)^4 × 50 × 0.002 = 8^4 × 0.1 = 4096 × 0.1 = 409.6
        $this->assertEqualsWithDelta(409.6, $result, 0.000001);
    }
}
