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
        Schema::create('stations', function (Blueprint $table) {
            $table->string('id')->primary(); // DWD station ID like '01048'
            $table->string('name');
            $table->string('location');
            $table->integer('elevation')->nullable();
            $table->integer('start_year');
            $table->integer('measurement_count')->default(0);
            $table->string('state');
            $table->date('latest_date')->nullable();
            $table->boolean('active')->default(true);
            $table->decimal('lat', 10, 6);
            $table->decimal('lon', 10, 6);
            $table->text('description')->nullable();
            $table->string('dwd_url')->nullable();
            $table->timestamps();
            
            // Add indexes for performance
            $table->index('lat');
            $table->index('lon');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stations');
    }
};
