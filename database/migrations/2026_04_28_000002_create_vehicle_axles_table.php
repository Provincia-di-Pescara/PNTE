<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_axles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('posizione')->comment('Numero d\'ordine asse (1, 2, 3…)');
            $table->string('tipo', 20);
            $table->unsignedInteger('interasse')->nullable()->comment('mm da asse precedente');
            $table->unsignedInteger('carico_tecnico')->comment('kg — carico max omologato');
            $table->timestamps();

            $table->unique(['vehicle_id', 'posizione']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_axles');
    }
};
