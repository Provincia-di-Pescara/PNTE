<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ApplicationServiceInterface;
use App\Contracts\ClearanceDispatchServiceInterface;
use App\Enums\ApplicationStatus;
use App\Models\Application;
use LogicException;

final class ApplicationService implements ApplicationServiceInterface
{
    public function __construct(
        private readonly ClearanceDispatchServiceInterface $clearanceDispatch,
    ) {}

    public function submit(Application $application): void
    {
        if (! $application->stato->canTransitionTo(ApplicationStatus::Submitted)) {
            throw new LogicException("Cannot transition from {$application->stato->value} to submitted.");
        }

        $application->stato = ApplicationStatus::Submitted;
        $application->save();

        $this->clearanceDispatch->dispatch($application);
    }

    public function markPaymentReady(Application $application): void
    {
        if (! $application->stato->canTransitionTo(ApplicationStatus::WaitingPayment)) {
            throw new LogicException("Cannot transition from {$application->stato->value} to waiting_payment.");
        }

        $application->stato = ApplicationStatus::WaitingPayment;
        $application->save();
    }

    public function approve(Application $application): void
    {
        if (! $application->stato->canTransitionTo(ApplicationStatus::Approved)) {
            throw new LogicException("Cannot transition from {$application->stato->value} to approved.");
        }

        $application->stato = ApplicationStatus::Approved;
        $application->save();
    }

    public function reject(Application $application, string $reason): void
    {
        if (! $application->stato->canTransitionTo(ApplicationStatus::Rejected)) {
            throw new LogicException("Cannot transition from {$application->stato->value} to rejected.");
        }

        $application->stato = ApplicationStatus::Rejected;
        $application->note = $reason;
        $application->save();
    }
}
