<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE tariffs ALTER COLUMN tipo_asse DROP NOT NULL');
        DB::statement("ALTER TABLE tariffs ADD COLUMN tipo_applicazione VARCHAR(25) NOT NULL DEFAULT 'analitica_km'");
        DB::statement('CREATE INDEX tariffs_tipo_app_from_idx ON tariffs (tipo_applicazione, valid_from)');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS tariffs_tipo_app_from_idx');
        DB::statement('ALTER TABLE tariffs DROP COLUMN IF EXISTS tipo_applicazione');
        DB::statement('ALTER TABLE tariffs ALTER COLUMN tipo_asse SET NOT NULL');
    }
};
