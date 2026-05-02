<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\EntityType;
use App\Models\Entity;
use Illuminate\Database\Seeder;

final class EntitySeeder extends Seeder
{
    public function run(): void
    {
        Entity::updateOrCreate(
            ['codice_istat' => '068'],
            [
                'nome' => 'Provincia di Pescara',
                'tipo' => EntityType::Provincia->value,
                'email' => 'info@provincia.pescara.it',
                'pec' => 'protocollo@pec.provincia.pescara.it',
            ]
        );

        Entity::updateOrCreate(
            ['codice_istat' => '068014'],
            [
                'nome' => 'Comune di Pescara',
                'tipo' => EntityType::Comune->value,
                'email' => 'info@comune.pescara.it',
                'pec' => 'protocollo@pec.comune.pescara.it',
            ]
        );

        Entity::updateOrCreate(
            ['nome' => 'ANAS SpA'],
            [
                'tipo' => EntityType::Anas->value,
                'codice_istat' => null,
                'email' => 'anas@anas.it',
                'pec' => 'anas@pec.anas.it',
            ]
        );
    }
}
