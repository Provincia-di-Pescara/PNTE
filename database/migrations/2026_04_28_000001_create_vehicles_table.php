<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('tipo', 20);
            $table->string('targa', 15)->unique();
            $table->string('numero_telaio', 17)->nullable();
            $table->string('marca', 100)->nullable();
            $table->string('modello', 100)->nullable();
            $table->smallInteger('anno_immatricolazione')->unsigned()->nullable();
            $table->unsignedInteger('massa_vuoto')->nullable()->comment('kg');
            $table->unsignedInteger('massa_complessiva')->nullable()->comment('kg — PTT');
            $table->unsignedInteger('lunghezza')->nullable()->comment('mm');
            $table->unsignedInteger('larghezza')->nullable()->comment('mm');
            $table->unsignedInteger('altezza')->nullable()->comment('mm');
            $table->unsignedTinyInteger('numero_assi')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
