<?php

declare(strict_types=1);

namespace App\Enums;

enum ApplicationStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case WaitingClearances = 'waiting_clearances';
    case WaitingPayment = 'waiting_payment';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Bozza',
            self::Submitted => 'Inviata',
            self::WaitingClearances => 'In attesa di nulla osta',
            self::WaitingPayment => 'In attesa di pagamento',
            self::Approved => 'Approvata',
            self::Rejected => 'Respinta',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Submitted => 'blue',
            self::WaitingClearances => 'yellow',
            self::WaitingPayment => 'orange',
            self::Approved => 'green',
            self::Rejected => 'red',
        };
    }

    public function isFinal(): bool
    {
        return match ($this) {
            self::Approved, self::Rejected => true,
            default => false,
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return match ($this) {
            self::Draft => $next === self::Submitted,
            self::Submitted => in_array($next, [self::WaitingClearances, self::WaitingPayment], true),
            self::WaitingClearances => in_array($next, [self::WaitingPayment, self::Rejected], true),
            self::WaitingPayment => in_array($next, [self::Approved, self::Rejected], true),
            self::Approved, self::Rejected => false,
        };
    }
}
