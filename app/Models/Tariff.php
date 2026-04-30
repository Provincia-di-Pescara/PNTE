<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AxleType;
use App\Enums\TipoApplicazioneTariff;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

final class Tariff extends Model
{
    use HasFactory;

    protected $fillable = [
        'tipo_asse',
        'tipo_applicazione',
        'coefficiente',
        'valid_from',
        'valid_to',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'tipo_asse' => AxleType::class,
            'tipo_applicazione' => TipoApplicazioneTariff::class,
            'coefficiente' => 'decimal:6',
            'valid_from' => 'date',
            'valid_to' => 'date',
        ];
    }

    /**
     * Scope: tariffe attive alla data odierna.
     *
     * @param  Builder<Tariff>  $query
     * @return Builder<Tariff>
     */
    public function scopeActive(Builder $query): Builder
    {
        $today = Carbon::today()->toDateString();

        return $query
            ->where('valid_from', '<=', $today)
            ->where(function (Builder $q) use ($today): void {
                $q->whereNull('valid_to')
                    ->orWhere('valid_to', '>=', $today);
            });
    }

    /**
     * Scope: tariffe per tipo di applicazione.
     *
     * @param  Builder<Tariff>  $query
     * @return Builder<Tariff>
     */
    public function scopeByTipoApplicazione(Builder $query, string|TipoApplicazioneTariff $tipo): Builder
    {
        $value = $tipo instanceof TipoApplicazioneTariff ? $tipo->value : $tipo;

        return $query->where('tipo_applicazione', $value);
    }
}
