<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TripStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Trip extends Model
{
    protected $fillable = [
        'application_id',
        'driver_user_id',
        'stato',
        'started_at',
        'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'stato' => TripStatus::class,
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_user_id');
    }

    public function isActive(): bool
    {
        return $this->stato === TripStatus::Active;
    }
}
