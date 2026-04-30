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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('ragione_sociale');
            $table->string('partita_iva', 11)->unique();
            $table->string('codice_fiscale', 16)->nullable()->unique();
            $table->string('indirizzo')->nullable();
            $table->string('comune')->nullable();
            $table->string('cap', 5)->nullable();
            $table->string('provincia', 2)->nullable();
            $table->string('email')->nullable();
            $table->string('pec')->nullable();
            $table->string('telefono', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
