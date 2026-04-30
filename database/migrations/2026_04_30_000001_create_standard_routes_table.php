<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('standard_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entity_id')->constrained()->cascadeOnDelete();
            $table->string('nome');
            $table->json('vehicle_types');
            $table->unsignedInteger('max_massa_kg')->nullable();
            $table->unsignedInteger('max_lunghezza_mm')->nullable();
            $table->unsignedInteger('max_larghezza_mm')->nullable();
            $table->unsignedInteger('max_altezza_mm')->nullable();
            $table->boolean('active')->default(true);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['entity_id', 'active']);
        });

        DB::statement('ALTER TABLE standard_routes ADD COLUMN geometry LINESTRING NOT NULL AFTER nome');
        DB::statement('CREATE SPATIAL INDEX standard_routes_geometry_idx ON standard_routes (geometry)');
    }

    public function down(): void
    {
        Schema::dropIfExists('standard_routes');
    }
};
