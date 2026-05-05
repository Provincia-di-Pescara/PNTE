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

        DB::statement('ALTER TABLE routes ADD COLUMN geometry geometry(LINESTRING, 4326) NOT NULL');
        DB::statement('CREATE INDEX routes_geometry_gix ON routes USING GIST (geometry)');
    }

    public function down(): void
    {
        Schema::dropIfExists('routes');
    }
};
