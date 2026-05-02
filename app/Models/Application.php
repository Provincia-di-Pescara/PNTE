<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ApplicationStatus;
use App\Enums\TipoIstanza;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_id',
        'route_id',
        'vehicle_id',
        'trailer_id',
        'stato',
        'tipo_istanza',
        'numero_viaggi',
        'valida_da',
        'valida_fino',
        'selected_entity_ids',
        'viaggi_effettuati',
        'sospesa_fino',
        'note',
        'wear_calculation',
    ];

    protected function casts(): array
    {
        return [
            'stato' => ApplicationStatus::class,
            'tipo_istanza' => TipoIstanza::class,
            'valida_da' => 'date',
            'valida_fino' => 'date',
            'selected_entity_ids' => 'array',
            'wear_calculation' => 'array',
            'sospesa_fino' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function trailer(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'trailer_id');
    }

    public function clearances(): HasMany
    {
        return $this->hasMany(Clearance::class);
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }

    public function scopeByStato(Builder $query, ApplicationStatus $stato): Builder
    {
        return $query->where('stato', $stato->value);
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('stato', ApplicationStatus::Draft->value);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('stato', [
            ApplicationStatus::Submitted->value,
            ApplicationStatus::WaitingClearances->value,
            ApplicationStatus::WaitingPayment->value,
        ]);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('stato', ApplicationStatus::Approved->value);
    }

    public function isSospesa(): bool
    {
        return $this->sospesa_fino !== null && $this->sospesa_fino->isFuture();
    }

    public function viaggiRimanenti(): int
    {
        $totale = $this->numero_viaggi ?? 1;

        return max(0, $totale - $this->viaggi_effettuati);
    }
}
