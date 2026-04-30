<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Enums\TipoApplicazioneTariff;
use PHPUnit\Framework\TestCase;

final class TipoApplicazioneTariffTest extends TestCase
{
    public function test_analitica_km_is_not_flat(): void
    {
        $this->assertFalse(TipoApplicazioneTariff::AnaliticaKm->isFlat());
    }

    public function test_forfettaria_agricola_is_flat(): void
    {
        $this->assertTrue(TipoApplicazioneTariff::ForfettariaAgricola->isFlat());
    }

    public function test_forfettaria_periodica_is_flat(): void
    {
        $this->assertTrue(TipoApplicazioneTariff::ForfettariaPeriodica->isFlat());
    }

    public function test_all_cases_have_labels(): void
    {
        foreach (TipoApplicazioneTariff::cases() as $case) {
            $this->assertNotEmpty($case->label());
        }
    }
}
