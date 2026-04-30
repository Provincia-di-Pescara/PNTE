<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Enums\VehicleType;
use PHPUnit\Framework\TestCase;

final class VehicleTypeTest extends TestCase
{
    public function test_agricultural_types_return_is_agricultural_true(): void
    {
        $agricultural = [
            VehicleType::TratticeAgricola,
            VehicleType::Mietitrebbia,
            VehicleType::MacchinaOperatrice,
            VehicleType::RimorchioAgricolo,
        ];

        foreach ($agricultural as $type) {
            $this->assertTrue($type->isAgricultural(), "{$type->value} should be agricultural");
        }
    }

    public function test_non_agricultural_types_return_is_agricultural_false(): void
    {
        $nonAgricultural = [
            VehicleType::Trattore,
            VehicleType::Rimorchio,
            VehicleType::Semirimorchio,
            VehicleType::MezzoDopera,
        ];

        foreach ($nonAgricultural as $type) {
            $this->assertFalse($type->isAgricultural(), "{$type->value} should not be agricultural");
        }
    }

    public function test_agricultural_types_have_labels(): void
    {
        $this->assertSame('Trattrice Agricola', VehicleType::TratticeAgricola->label());
        $this->assertSame('Mietitrebbia / Raccoglitrice', VehicleType::Mietitrebbia->label());
        $this->assertSame('Macchina Operatrice', VehicleType::MacchinaOperatrice->label());
        $this->assertSame('Rimorchio Agricolo', VehicleType::RimorchioAgricolo->label());
    }
}
