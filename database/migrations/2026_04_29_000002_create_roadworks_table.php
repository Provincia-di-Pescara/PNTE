<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roadworks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entity_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->date('valid_from');
            $table->date('valid_to')->nullable();
            $table->string('severity', 20);
            $table->string('status', 20);
            $table->text('note')->nullable();
            $table->timestamps();
        });

        DB::statement('ALTER TABLE roadworks ADD COLUMN geometry LINESTRING NOT NULL AFTER title');
        DB::statement('CREATE SPATIAL INDEX roadworks_geometry_idx ON roadworks (geometry)');
    }

    public function down(): void
    {
        Schema::dropIfExists('roadworks');
    }
};
