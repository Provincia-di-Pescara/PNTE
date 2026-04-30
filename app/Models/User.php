<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AuthProvider;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Lab404\Impersonate\Models\Impersonate;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Impersonate, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'codice_fiscale',
        'auth_provider',
        'provider_id',
        'nome_verificato',
        'cognome_verificato',
        'data_nascita',
        'luogo_nascita',
        'sesso',
        'entity_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'auth_provider' => AuthProvider::class,
            'data_nascita' => 'date',
            'entity_id' => 'integer',
        ];
    }

    /** @return BelongsTo<Entity, $this> */
    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    /** @return BelongsToMany<Company, $this> */
    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class)
            ->withPivot('role', 'valid_from', 'valid_to', 'approved_at', 'approved_by')
            ->withTimestamps();
    }

    public function isCitizen(): bool
    {
        return $this->hasRole(UserRole::Citizen->value);
    }

    public function isOperator(): bool
    {
        return $this->hasRole(UserRole::Operator->value)
            || $this->hasRole(UserRole::SuperAdmin->value);
    }

    public function isThirdParty(): bool
    {
        return $this->hasRole(UserRole::ThirdParty->value);
    }

    public function canImpersonate(): bool
    {
        return $this->hasRole(UserRole::SuperAdmin->value);
    }

    public function canBeImpersonated(): bool
    {
        return ! $this->hasRole(UserRole::SuperAdmin->value);
    }

    /** @return HasMany<ImpersonationLog, $this> */
    public function impersonationLogsAsImpersonator(): HasMany
    {
        return $this->hasMany(ImpersonationLog::class, 'impersonator_id');
    }
}
