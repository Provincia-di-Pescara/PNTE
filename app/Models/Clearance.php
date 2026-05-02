<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ClearanceStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Clearance extends Model
{
    protected $fillable = [
        'application_id',
        'entity_id',
        'stato',
        'note',
        'decided_at',
        'decided_by',
    ];

    protected function casts(): array
    {
        return [
            'stato' => ClearanceStatus::class,
            'decided_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    public function decidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}
