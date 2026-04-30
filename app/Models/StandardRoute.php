<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

final class StandardRoute extends Model
{
    use HasFactory;

    protected $fillable = [
        'entity_id',
        'nome',
        'vehicle_types',
        'max_massa_kg',
        'max_lunghezza_mm',
        'max_larghezza_mm',
        'max_altezza_mm',
        'active',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'entity_id' => 'integer',
            'vehicle_types' => 'array',
            'max_massa_kg' => 'integer',
            'max_lunghezza_mm' => 'integer',
            'max_larghezza_mm' => 'integer',
            'max_altezza_mm' => 'integer',
            'active' => 'boolean',
        ];
    }

    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    public function getRawWkt(): ?string
    {
        if (! $this->exists) {
            return null;
        }

        return DB::scalar('SELECT ST_AsText(geometry) FROM standard_routes WHERE id = ?', [$this->id]);
    }
}
