<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('route_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trailer_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->string('stato')->default('draft');
            $table->string('tipo_istanza');
            $table->unsignedInteger('numero_viaggi')->nullable();
            $table->date('valida_da');
            $table->date('valida_fino');
            $table->json('selected_entity_ids')->nullable();
            $table->unsignedInteger('viaggi_effettuati')->default(0);
            $table->dateTime('sospesa_fino')->nullable();
            $table->text('note')->nullable();
            $table->json('wear_calculation')->nullable();
            $table->timestamps();

            $table->index('stato');
            $table->index('user_id');
            $table->index('company_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
