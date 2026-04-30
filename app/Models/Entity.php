<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EntityType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class Entity extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'tipo',
        'codice_istat',
        'geom',
        'pec',
        'email',
        'telefono',
        'indirizzo',
        'codice_fisc_piva',
        'codice_sdi',
        'codice_univoco_ainop',
    ];

    protected function casts(): array
    {
        return [
            'tipo' => EntityType::class,
        ];
    }
}
