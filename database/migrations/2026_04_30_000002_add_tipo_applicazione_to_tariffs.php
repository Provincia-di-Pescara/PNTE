<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE tariffs MODIFY COLUMN tipo_asse VARCHAR(20) NULL');
        DB::statement("ALTER TABLE tariffs ADD COLUMN tipo_applicazione VARCHAR(25) NOT NULL DEFAULT 'analitica_km' AFTER tipo_asse");
        DB::statement('CREATE INDEX tariffs_tipo_app_from_idx ON tariffs (tipo_applicazione, valid_from)');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX tariffs_tipo_app_from_idx ON tariffs');
        DB::statement('ALTER TABLE tariffs DROP COLUMN tipo_applicazione');
        DB::statement('ALTER TABLE tariffs MODIFY COLUMN tipo_asse VARCHAR(20) NOT NULL');
    }
};
