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
        Schema::create('measurements', function (Blueprint $table) {
            $table->id();
            $table->string('station_id');
            $table->date('date');
            $table->decimal('temp_max', 5, 1)->nullable();
            $table->decimal('temp_min', 5, 1)->nullable();
            $table->decimal('temp_mean', 5, 1)->nullable();
            $table->decimal('precipitation', 6, 1)->nullable();
            $table->decimal('sunshine', 5, 1)->nullable();
            $table->decimal('snow_depth', 5, 1)->nullable();
            $table->string('quality_flags', 10)->nullable();
            $table->timestamps();
            
            // Foreign key constraint
            $table->foreign('station_id')->references('id')->on('stations')->onDelete('cascade');
            
            // Composite unique index to prevent duplicate measurements
            $table->unique(['station_id', 'date']);
            
            // Indexes for common queries
            $table->index('station_id');
            $table->index('date');
            $table->index(['station_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('measurements');
    }
};
