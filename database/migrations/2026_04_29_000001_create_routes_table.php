<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->json('waypoints');
            $table->decimal('distance_km', 8, 3)->nullable();
            $table->json('entity_breakdown')->nullable();
            $table->timestamps();
        });

        DB::statement('ALTER TABLE routes ADD COLUMN geometry LINESTRING NOT NULL AFTER waypoints');
        DB::statement('CREATE SPATIAL INDEX routes_geometry_idx ON routes (geometry)');
    }

    public function down(): void
    {
        Schema::dropIfExists('routes');
    }
};
