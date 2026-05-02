<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'ragione_sociale',
        'partita_iva',
        'codice_fiscale',
        'indirizzo',
        'comune',
        'cap',
        'provincia',
        'email',
        'pec',
        'telefono',
        'infocamere_verified_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'infocamere_verified_at' => 'datetime',
        ];
    }

    /** @return BelongsToMany<User, $this> */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role', 'valid_from', 'valid_to', 'approved_at', 'approved_by')
            ->withTimestamps();
    }

    /** @return Collection<int, User> */
    public function approvedUsers(): Collection
    {
        return $this->users()
            ->wherePivotNotNull('approved_at')
            ->get();
    }

    /** @return HasMany<Vehicle, $this> */
    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }
}
