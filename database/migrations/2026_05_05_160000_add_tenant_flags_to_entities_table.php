<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entities', function (Blueprint $table): void {
            $table->boolean('is_tenant')->default(false)->after('codice_univoco_ainop');
            $table->boolean('has_financial_delegation')->default(false)->after('is_tenant');
            $table->boolean('is_capofila')->default(false)->after('has_financial_delegation');
        });
    }

    public function down(): void
    {
        Schema::table('entities', function (Blueprint $table): void {
            $table->dropColumn(['is_tenant', 'has_financial_delegation', 'is_capofila']);
        });
    }
};
