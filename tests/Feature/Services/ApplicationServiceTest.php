<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Contracts\ClearanceDispatchServiceInterface;
use App\Enums\ApplicationStatus;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\Setting;
use App\Services\ApplicationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Mockery;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

final class ApplicationServiceTest extends TestCase
{
    use RefreshDatabase;

    private ApplicationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (UserRole::cases() as $role) {
            Role::firstOrCreate(['name' => $role->value, 'guard_name' => 'web']);
        }

        Setting::set('setup_completed', '1');

        $mockDispatch = Mockery::mock(ClearanceDispatchServiceInterface::class);
        $mockDispatch->shouldReceive('dispatch')->andReturn(null);

        $this->service = new ApplicationService($mockDispatch);
    }

    public function test_submit_transitions_draft_to_submitted(): void
    {
        $application = Application::factory()->draft()->create();

        $this->service->submit($application);

        $this->assertEquals(ApplicationStatus::Submitted, $application->stato);
    }

    public function test_submit_throws_when_not_draft(): void
    {
        $this->expectException(LogicException::class);

        $application = Application::factory()->submitted()->create();
        $this->service->submit($application);
    }

    public function test_mark_payment_ready_transitions_submitted_to_waiting_payment(): void
    {
        $application = Application::factory()->submitted()->create();

        $this->service->markPaymentReady($application);

        $this->assertEquals(ApplicationStatus::WaitingPayment, $application->stato);
    }

    public function test_approve_transitions_waiting_payment_to_approved(): void
    {
        $application = Application::factory()->create(['stato' => ApplicationStatus::WaitingPayment]);

        $this->service->approve($application);

        $this->assertEquals(ApplicationStatus::Approved, $application->stato);
    }

    public function test_approve_throws_when_not_waiting_payment(): void
    {
        $this->expectException(LogicException::class);

        $application = Application::factory()->draft()->create();
        $this->service->approve($application);
    }

    public function test_reject_transitions_waiting_payment_to_rejected(): void
    {
        $application = Application::factory()->create(['stato' => ApplicationStatus::WaitingPayment]);

        $this->service->reject($application, 'Motivo rifiuto');

        $this->assertEquals(ApplicationStatus::Rejected, $application->stato);
        $this->assertEquals('Motivo rifiuto', $application->note);
    }

    public function test_reject_throws_when_already_approved(): void
    {
        $this->expectException(LogicException::class);

        $application = Application::factory()->approved()->create();
        $this->service->reject($application, 'Troppo tardi');
    }
}
