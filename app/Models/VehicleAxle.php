<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AxleType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class VehicleAxle extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'posizione',
        'tipo',
        'interasse',
        'carico_tecnico',
    ];

    protected function casts(): array
    {
        return [
            'tipo' => AxleType::class,
            'posizione' => 'integer',
            'interasse' => 'integer',
            'carico_tecnico' => 'integer',
        ];
    }

    /** @return BelongsTo<Vehicle, $this> */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
