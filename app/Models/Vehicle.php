<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\VehicleType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'tipo',
        'targa',
        'numero_telaio',
        'marca',
        'modello',
        'anno_immatricolazione',
        'massa_vuoto',
        'massa_complessiva',
        'lunghezza',
        'larghezza',
        'altezza',
        'numero_assi',
    ];

    protected function casts(): array
    {
        return [
            'tipo' => VehicleType::class,
            'anno_immatricolazione' => 'integer',
        ];
    }

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** @return HasMany<VehicleAxle, $this> */
    public function axles(): HasMany
    {
        return $this->hasMany(VehicleAxle::class)->orderBy('posizione');
    }
}
