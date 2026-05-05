<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Enums\ApplicationStatus;
use PHPUnit\Framework\TestCase;

final class ApplicationStatusTest extends TestCase
{
    public function test_all_cases_have_labels(): void
    {
        foreach (ApplicationStatus::cases() as $case) {
            $this->assertNotEmpty($case->label());
        }
    }

    public function test_all_cases_have_colors(): void
    {
        foreach (ApplicationStatus::cases() as $case) {
            $this->assertNotEmpty($case->color());
        }
    }

    public function test_approved_and_rejected_are_final(): void
    {
        $this->assertTrue(ApplicationStatus::Approved->isFinal());
        $this->assertTrue(ApplicationStatus::Rejected->isFinal());
    }

    public function test_non_final_statuses_are_not_final(): void
    {
        $this->assertFalse(ApplicationStatus::Draft->isFinal());
        $this->assertFalse(ApplicationStatus::Submitted->isFinal());
        $this->assertFalse(ApplicationStatus::WaitingClearances->isFinal());
        $this->assertFalse(ApplicationStatus::WaitingPayment->isFinal());
    }

    public function test_draft_can_transition_to_submitted(): void
    {
        $this->assertTrue(ApplicationStatus::Draft->canTransitionTo(ApplicationStatus::Submitted));
    }

    public function test_draft_cannot_transition_to_approved(): void
    {
        $this->assertFalse(ApplicationStatus::Draft->canTransitionTo(ApplicationStatus::Approved));
    }

    public function test_submitted_can_transition_to_waiting_clearances(): void
    {
        $this->assertTrue(ApplicationStatus::Submitted->canTransitionTo(ApplicationStatus::WaitingClearances));
    }

    public function test_submitted_can_transition_to_waiting_payment(): void
    {
        $this->assertTrue(ApplicationStatus::Submitted->canTransitionTo(ApplicationStatus::WaitingPayment));
    }

    public function test_waiting_clearances_can_transition_to_waiting_payment(): void
    {
        $this->assertTrue(ApplicationStatus::WaitingClearances->canTransitionTo(ApplicationStatus::WaitingPayment));
    }

    public function test_waiting_clearances_can_transition_to_rejected(): void
    {
        $this->assertTrue(ApplicationStatus::WaitingClearances->canTransitionTo(ApplicationStatus::Rejected));
    }

    public function test_waiting_payment_can_transition_to_approved(): void
    {
        $this->assertTrue(ApplicationStatus::WaitingPayment->canTransitionTo(ApplicationStatus::Approved));
    }

    public function test_approved_cannot_transition_to_anything(): void
    {
        foreach (ApplicationStatus::cases() as $target) {
            $this->assertFalse(ApplicationStatus::Approved->canTransitionTo($target));
        }
    }

    public function test_rejected_cannot_transition_to_anything(): void
    {
        foreach (ApplicationStatus::cases() as $target) {
            $this->assertFalse(ApplicationStatus::Rejected->canTransitionTo($target));
        }
    }
}
