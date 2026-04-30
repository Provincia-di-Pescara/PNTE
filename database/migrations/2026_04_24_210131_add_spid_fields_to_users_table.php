<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('codice_fiscale', 16)->nullable()->unique()->after('email');
            $table->string('auth_provider', 10)->default('local')->after('codice_fiscale');
            $table->string('provider_id')->nullable()->after('auth_provider');
            $table->string('nome_verificato')->nullable()->after('provider_id');
            $table->string('cognome_verificato')->nullable()->after('nome_verificato');
            $table->date('data_nascita')->nullable()->after('cognome_verificato');
            $table->string('luogo_nascita')->nullable()->after('data_nascita');
            $table->string('sesso', 1)->nullable()->after('luogo_nascita');
            $table->string('password')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['codice_fiscale']);
            $table->dropColumn([
                'codice_fiscale', 'auth_provider', 'provider_id',
                'nome_verificato', 'cognome_verificato',
                'data_nascita', 'luogo_nascita', 'sesso',
            ]);
            $table->string('password')->nullable(false)->change();
        });
    }
};
