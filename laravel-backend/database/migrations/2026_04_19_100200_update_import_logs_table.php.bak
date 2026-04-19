<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('import_logs', function (Blueprint $table) {
            // Rename timestamp to import_type if it exists
            if (Schema::hasColumn('import_logs', 'timestamp')) {
                $table->renameColumn('timestamp', 'import_type');
            } else {
                $table->string('import_type')->nullable()->after('id');
            }
            
            // Add missing columns
            if (!Schema::hasColumn('import_logs', 'records_imported')) {
                $table->integer('records_imported')->default(0)->after('records_processed');
            }
            
            if (!Schema::hasColumn('import_logs', 'records_skipped')) {
                $table->integer('records_skipped')->default(0)->after('records_imported');
            }
            
            if (!Schema::hasColumn('import_logs', 'records_failed')) {
                $table->integer('records_failed')->default(0)->after('records_skipped');
            }
            
            if (!Schema::hasColumn('import_logs', 'parameters')) {
                $table->json('parameters')->nullable()->after('error_message');
            }
            
            if (!Schema::hasColumn('import_logs', 'user_initiated')) {
                $table->boolean('user_initiated')->default(false)->after('parameters');
            }
            
            if (!Schema::hasColumn('import_logs', 'updated_at')) {
                $table->timestamp('updated_at')->nullable()->after('created_at');
            }
        });
        
        // Try to add indexes (they might already exist)
        try {
            Schema::table('import_logs', function (Blueprint $table) {
                $table->index(['import_type', 'created_at']);
                $table->index(['station_id', 'created_at']);
                $table->index(['success', 'created_at']);
            });
        } catch (\Exception $e) {
            // Indexes might already exist, ignore the error
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('import_logs', function (Blueprint $table) {
            // We won't reverse this migration as it's fixing the table structure
            // In production, you would need to handle this carefully
        });
    }
};