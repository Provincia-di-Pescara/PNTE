<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tariffs', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_asse', 20);
            $table->decimal('coefficiente', 10, 6)->comment('Coefficiente D.P.R. 495/1992');
            $table->date('valid_from');
            $table->date('valid_to')->nullable()->comment('NULL = versione attiva');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['tipo_asse', 'valid_from']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tariffs');
    }
};
