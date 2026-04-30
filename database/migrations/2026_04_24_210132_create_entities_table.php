<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('entities', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('tipo', 20);
            $table->string('codice_istat', 10)->nullable()->unique();
            $table->string('pec')->nullable();
            $table->string('email')->nullable();
            $table->string('telefono', 50)->nullable();
            $table->string('indirizzo')->nullable();
            $table->string('codice_fisc_piva', 16)->nullable();
            $table->string('codice_sdi', 7)->nullable();
            $table->timestamps();
        });

        DB::statement('ALTER TABLE entities ADD COLUMN geom MULTIPOLYGON NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entities');
    }
};
