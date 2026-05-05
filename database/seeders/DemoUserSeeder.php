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

        // 1. system-admin — infra/IT operator, local login only, no entity binding
        User::updateOrCreate(
            ['email' => 'system@gte.interno'],
            [
                'name' => 'Amministratore Sistema',
                'password' => Hash::make('password'),
                'entity_id' => null,
            ]
        )->syncRoles([UserRole::SystemAdmin->value]);

        // 2. admin-capofila — Provincia di Pescara (is_capofila entity)
        User::updateOrCreate(
            ['email' => 'capofila@provincia.pe.it'],
            [
                'name' => 'Responsabile Capofila',
                'password' => Hash::make('password'),
                'entity_id' => $provincia?->id,
            ]
        )->syncRoles([UserRole::AdminCapofila->value]);

        // 3. admin-ente — Provincia di Pescara standard
        User::updateOrCreate(
            ['email' => 'admin@provincia.pe.it'],
            [
                'name' => 'Admin Provincia Pescara',
                'password' => Hash::make('password'),
                'entity_id' => $provincia?->id,
            ]
        )->syncRoles([UserRole::AdminEnte->value]);

        // 4. operator (entity-bound) — Comune di Pescara
        User::updateOrCreate(
            ['email' => 'operatore@comune.pescara.it'],
            [
                'name' => 'Operatore Comune Pescara',
                'password' => Hash::make('password'),
                'entity_id' => $comune?->id,
            ]
        )->syncRoles([UserRole::Operator->value]);

        // 5. third-party — ANAS SpA
        User::updateOrCreate(
            ['email' => 'tecnico@anas.it'],
            [
                'name' => 'Tecnico ANAS',
                'password' => Hash::make('password'),
                'entity_id' => $anas?->id,
            ]
        )->syncRoles([UserRole::ThirdParty->value]);

        // 6. admin-azienda — transport company principal
        $admin = User::updateOrCreate(
            ['email' => 'mario.rossi@trasporti.it'],
            [
                'name' => 'Mario Rossi',
                'password' => Hash::make('password'),
                'entity_id' => null,
            ]
        );
        $admin->syncRoles([UserRole::AdminAzienda->value]);

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

        if (! $company->users()->where('user_id', $admin->id)->exists()) {
            $company->users()->attach($admin->id, [
                'role' => DelegationRole::Titolare->value,
                'valid_from' => now(),
                'approved_at' => now(),
                'approved_by' => $admin->id,
            ]);
        }

        // 7. citizen — individual submitting requests
        User::updateOrCreate(
            ['email' => 'cittadino@example.it'],
            [
                'name' => 'Giuseppe Verdi',
                'password' => Hash::make('password'),
                'entity_id' => null,
            ]
        )->syncRoles([UserRole::Citizen->value]);

        // 8. law-enforcement — Polizia Stradale
        User::updateOrCreate(
            ['email' => 'agente@polstrada.it'],
            [
                'name' => 'Agente Polizia Stradale',
                'password' => Hash::make('password'),
                'entity_id' => null,
            ]
        )->syncRoles([UserRole::LawEnforcement->value]);
    }
}
