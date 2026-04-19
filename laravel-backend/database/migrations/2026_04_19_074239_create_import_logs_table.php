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
        Schema::create('import_logs', function (Blueprint $table) {
            $table->id();
            $table->string('import_type'); // 'historical', 'recent', 'full', 'station_add'
            $table->string('station_id')->nullable(); // Foreign key to stations table
            $table->string('operation'); // 'download', 'parse', 'import', 'update'
            $table->integer('records_processed')->default(0);
            $table->integer('records_imported')->default(0);
            $table->integer('records_skipped')->default(0);
            $table->integer('records_failed')->default(0);
            $table->boolean('success')->default(false);
            $table->float('duration_seconds')->nullable();
            $table->text('error_message')->nullable();
            $table->json('parameters')->nullable(); // JSON with import parameters
            $table->boolean('user_initiated')->default(false);
            $table->timestamps();
            
            // Foreign key constraint
            $table->foreign('station_id')->references('id')->on('stations')->onDelete('cascade');
            
            // Indexes for performance
            $table->index(['import_type', 'created_at']);
            $table->index(['station_id', 'created_at']);
            $table->index(['success', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_logs');
    }
};
