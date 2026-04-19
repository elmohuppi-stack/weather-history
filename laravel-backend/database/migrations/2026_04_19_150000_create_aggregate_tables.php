<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Erstelle Tabellen für monatliche und jährliche Wetterdaten-Aggregate
     * Diese Tabellen speichern vorberechnete Statistiken statt diese on-the-fly zu berechnen
     */
    public function up(): void
    {
        // Monatliche Aggregate
        Schema::create('monthly_aggregates', function (Blueprint $table) {
            $table->id();
            $table->string('station_id', 10);
            $table->integer('year');
            $table->unsignedTinyInteger('month');
            
            // Temperatur (°C)
            $table->float('temp_max_absolute')->nullable();
            $table->float('temp_min_absolute')->nullable();
            $table->float('temp_mean')->nullable();
            
            // Niederschlag (mm)
            $table->float('precipitation_sum')->nullable();
            
            // Sonnenschein (Stunden)
            $table->float('sunshine_hours')->nullable();
            
            // Schnee
            $table->float('snow_depth_max')->nullable();
            
            // Tage-Kategorien
            $table->unsignedSmallInteger('frost_days')->default(0);
            $table->unsignedSmallInteger('summer_days')->default(0);
            $table->unsignedSmallInteger('rainy_days')->default(0);
            $table->unsignedSmallInteger('snowy_days')->default(0);
            
            // Datenqualität
            $table->unsignedSmallInteger('records_count')->default(0);
            $table->unsignedSmallInteger('valid_records')->default(0);
            
            // Metadaten
            $table->timestamps();
        });
        
        // Jährliche Aggregate
        Schema::create('yearly_aggregates', function (Blueprint $table) {
            $table->id();
            $table->string('station_id', 10);
            $table->integer('year');
            
            // Temperatur (°C)
            $table->float('temp_max_absolute')->nullable();
            $table->float('temp_min_absolute')->nullable();
            $table->float('temp_mean')->nullable();
            
            // Niederschlag (mm)
            $table->float('precipitation_sum')->nullable();
            
            // Sonnenschein (Stunden)
            $table->float('sunshine_hours')->nullable();
            
            // Schnee
            $table->float('snow_depth_max')->nullable();
            
            // Tage-Kategorien
            $table->unsignedSmallInteger('frost_days')->default(0);
            $table->unsignedSmallInteger('summer_days')->default(0);
            $table->unsignedSmallInteger('rainy_days')->default(0);
            $table->unsignedSmallInteger('snowy_days')->default(0);
            
            // Datenqualität
            $table->unsignedSmallInteger('records_count')->default(0);
            $table->unsignedSmallInteger('valid_records')->default(0);
            
            // Metadaten
            $table->timestamps();
        });
        
        // Klima-Normen (30-Jahres-Durchschnitte)
        Schema::create('climate_normals', function (Blueprint $table) {
            $table->id();
            $table->string('station_id', 10);
            $table->unsignedTinyInteger('month');
            
            // Temperatur (°C) - 30-Jahres-Durchschnitte
            $table->float('temp_mean')->nullable();
            $table->float('temp_max_mean')->nullable();
            $table->float('temp_min_mean')->nullable();
            
            // Niederschlag (mm)
            $table->float('precipitation_mean')->nullable();
            
            // Sonnenschein (Stunden)
            $table->float('sunshine_hours_mean')->nullable();
            
            // Referenzzeitraum
            $table->integer('reference_period_start')->default(1991);
            $table->integer('reference_period_end')->default(2020);
            
            // Metadaten
            $table->timestamps();
        });
        
        // Indexes und Constraints hinzufügen (nach Tabellenerstellung)
        Schema::table('monthly_aggregates', function (Blueprint $table) {
            $table->foreign('station_id')->references('id')->on('stations')->onDelete('cascade');
            $table->unique(['station_id', 'year', 'month']);
            $table->index(['station_id', 'year']);
        });
        
        Schema::table('yearly_aggregates', function (Blueprint $table) {
            $table->foreign('station_id')->references('id')->on('stations')->onDelete('cascade');
            $table->unique(['station_id', 'year']);
            $table->index(['station_id', 'year']);
        });
        
        Schema::table('climate_normals', function (Blueprint $table) {
            $table->foreign('station_id')->references('id')->on('stations')->onDelete('cascade');
            $table->unique(['station_id', 'month']);
        });
    }

    /**
     * Rollback der Migration
     */
    public function down(): void
    {
        Schema::dropIfExists('climate_normals');
        Schema::dropIfExists('yearly_aggregates');
        Schema::dropIfExists('monthly_aggregates');
    }
};

