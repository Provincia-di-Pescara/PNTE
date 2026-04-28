<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AxleType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

final class Tariff extends Model
{
    use HasFactory;

    protected $fillable = [
        'tipo_asse',
        'coefficiente',
        'valid_from',
        'valid_to',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'tipo_asse' => AxleType::class,
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
}
