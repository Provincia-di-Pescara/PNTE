<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

final class Route extends Model
{
    use HasFactory;

    protected $table = 'routes';

    protected $fillable = [
        'user_id',
        'waypoints',
        'geometry',
        'distance_km',
        'entity_breakdown',
    ];

    protected function casts(): array
    {
        return [
            'waypoints' => 'array',
            'entity_breakdown' => 'array',
            'distance_km' => 'decimal:3',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getRawGeometry(): string
    {
        return (string) DB::scalar('SELECT ST_AsText(geometry) FROM routes WHERE id = ?', [$this->id]);
    }
}
