<?php

declare(strict_types=1);

namespace App\Enums;

enum ClearanceStatus: string
{
    case Pending = 'pending';
    case PreCleared = 'pre_cleared';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'In attesa',
            self::PreCleared => 'Pre-approvato (ARS)',
            self::Approved => 'Approvato',
            self::Rejected => 'Rifiutato',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'yellow',
            self::PreCleared => 'blue',
            self::Approved => 'green',
            self::Rejected => 'red',
        };
    }

    public function isDecided(): bool
    {
        return match ($this) {
            self::Approved, self::Rejected, self::PreCleared => true,
            self::Pending => false,
        };
    }

    public function isPositive(): bool
    {
        return match ($this) {
            self::Approved, self::PreCleared => true,
            self::Pending, self::Rejected => false,
        };
    }
}
