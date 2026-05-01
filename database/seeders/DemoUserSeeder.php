<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\DelegationRole;
use App\Enums\UserRole;
use App\Models\Company;
use App\Models\Entity;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $provincia = Entity::where('codice_istat', '068')->first();
        $comune = Entity::where('codice_istat', '068014')->first();
        $anas = Entity::where('nome', 'ANAS SpA')->first();

        // 1. Super-admin — Provincia di Pescara
        $admin = User::updateOrCreate(
            ['email' => 'admin@provincia.pe.it'],
            [
                'name' => 'Admin Provincia',
                'password' => Hash::make('password'),
                'entity_id' => $provincia?->id,
            ]
        );
        $admin->syncRoles([UserRole::SuperAdmin->value]);

        // 2. Third-party — Comune di Pescara
        $comune_user = User::updateOrCreate(
            ['email' => 'tecnico@comune.pescara.it'],
            [
                'name' => 'Tecnico Comune Pescara',
                'password' => Hash::make('password'),
                'entity_id' => $comune?->id,
            ]
        );
        $comune_user->syncRoles([UserRole::ThirdParty->value]);

        // 3. Third-party — ANAS SpA
        $anas_user = User::updateOrCreate(
            ['email' => 'tecnico@anas.it'],
            [
                'name' => 'Tecnico ANAS',
                'password' => Hash::make('password'),
                'entity_id' => $anas?->id,
            ]
        );
        $anas_user->syncRoles([UserRole::ThirdParty->value]);

        // 4. Citizen — transport company
        $citizen = User::updateOrCreate(
            ['email' => 'mario.rossi@trasporti.it'],
            [
                'name' => 'Mario Rossi',
                'password' => Hash::make('password'),
                'entity_id' => null,
            ]
        );
        $citizen->syncRoles([UserRole::Citizen->value]);

        $company = Company::updateOrCreate(
            ['partita_iva' => '01234567890'],
            [
                'ragione_sociale' => 'Trasporti Rossi Srl',
                'codice_fiscale' => '01234567890',
                'email' => 'info@trasporti-rossi.it',
                'comune' => 'Pescara',
                'provincia' => 'PE',
                'cap' => '65100',
            ]
        );

        if (! $company->users()->where('user_id', $citizen->id)->exists()) {
            $company->users()->attach($citizen->id, [
                'role' => DelegationRole::Titolare->value,
                'valid_from' => now(),
                'approved_at' => now(),
                'approved_by' => $admin->id,
            ]);
        }

        // 5. Law enforcement
        $agent = User::updateOrCreate(
            ['email' => 'agente@polstrada.it'],
            [
                'name' => 'Agente Polizia Stradale',
                'password' => Hash::make('password'),
                'entity_id' => null,
            ]
        );
        $agent->syncRoles([UserRole::LawEnforcement->value]);
    }
}
