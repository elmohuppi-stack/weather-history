<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For PostgreSQL, we need to use raw SQL to change column type
        DB::statement('ALTER TABLE import_logs ALTER COLUMN import_type TYPE VARCHAR(50) USING import_type::VARCHAR(50)');
        DB::statement('ALTER TABLE import_logs ALTER COLUMN import_type DROP DEFAULT');
        
        // Update any date values to 'historical'
        DB::table('import_logs')
            ->where('import_type', '~', '^\d{4}-\d{2}-\d{2}$')
            ->update(['import_type' => 'historical']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We can't safely reverse this without knowing the original state
        // In production, you would need to backup first
        DB::statement('ALTER TABLE import_logs ALTER COLUMN import_type TYPE DATE USING import_type::DATE');
        DB::statement('ALTER TABLE import_logs ALTER COLUMN import_type SET DEFAULT CURRENT_DATE');
    }
};