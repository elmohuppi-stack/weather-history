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
            $table->year('year');
            $table->unsignedTinyInteger('month'); // 1-12
            $table->unique(['station_id', 'year', 'month']);
            
            // Temperatur (°C)
            $table->float('temp_max_absolute')->nullable(); // Höchsttemperatur des Monats
            $table->float('temp_min_absolute')->nullable(); // Tiefsttemperatur des Monats
            $table->float('temp_mean')->nullable();         // Monatsmittel
            
            // Niederschlag (mm)
            $table->float('precipitation_sum')->nullable();
            
            // Sonnenschein (Stunden)
            $table->float('sunshine_hours')->nullable();
            
            // Schnee
            $table->float('snow_depth_max')->nullable();
            
            // Tage-Kategorien
            $table->unsignedSmallInteger('frost_days')->default(0);      // Tage mit Tmin < 0°C
            $table->unsignedSmallInteger('summer_days')->default(0);     // Tage mit Tmax > 25°C
            $table->unsignedSmallInteger('rainy_days')->default(0);      // Tage mit Regen > 0.1mm
            $table->unsignedSmallInteger('snowy_days')->default(0);      // Tage mit Schnee > 0cm
            
            // Datenqualität
            $table->unsignedSmallInteger('records_count')->default(0);   // Anzahl Datensätze im Monat
            $table->unsignedSmallInteger('valid_records')->default(0);   // Gültige Datensätze
            
            // Metadaten
            $table->timestamps();
            $table->foreign('station_id')->references('id')->on('stations')->onDelete('cascade');
            $table->index(['station_id', 'year']);
        });
        
        // Jährliche Aggregate
        Schema::create('yearly_aggregates', function (Blueprint $table) {
            $table->id();
            $table->string('station_id', 10);
            $table->year('year');
            $table->unique(['station_id', 'year']);
            
            // Temperatur (°C)
            $table->float('temp_max_absolute')->nullable(); // Jahreshöchsttemperatur
            $table->float('temp_min_absolute')->nullable(); // Jahrestiefsttemperatur
            $table->float('temp_mean')->nullable();         // Jahresmittel
            
            // Niederschlag (mm)
            $table->float('precipitation_sum')->nullable(); // Jahressumme
            
            // Sonnenschein (Stunden)
            $table->float('sunshine_hours')->nullable();    // Jahressumme
            
            // Schnee
            $table->float('snow_depth_max')->nullable();
            
            // Tage-Kategorien
            $table->unsignedSmallInteger('frost_days')->default(0);      // Tage mit Tmin < 0°C
            $table->unsignedSmallInteger('summer_days')->default(0);     // Tage mit Tmax > 25°C
            $table->unsignedSmallInteger('rainy_days')->default(0);      // Tage mit Regen > 0.1mm
            $table->unsignedSmallInteger('snowy_days')->default(0);      // Tage mit Schnee > 0cm
            
            // Datenqualität
            $table->unsignedSmallInteger('records_count')->default(0);   // Anzahl Tage im Jahr
            $table->unsignedSmallInteger('valid_records')->default(0);   // Gültige Datensätze
            
            // Metadaten
            $table->timestamps();
            $table->foreign('station_id')->references('id')->on('stations')->onDelete('cascade');
            $table->index(['station_id', 'year']);
        });
        
        // Klima-Normen (30-Jahres-Durchschnitte: 1991-2020 Standard)
        Schema::create('climate_normals', function (Blueprint $table) {
            $table->id();
            $table->string('station_id', 10);
            $table->unsignedTinyInteger('month'); // 1-12 für monatliche Normen, oder 0 für Jahreswert
            $table->unique(['station_id', 'month']);
            
            // Temperatur (°C) - 30-Jahres-Durchschnitte
            $table->float('temp_mean')->nullable();         // Normaltemperatur
            $table->float('temp_max_mean')->nullable();     // Normale Tagesmaximum
            $table->float('temp_min_mean')->nullable();     // Normale Tagesminimum
            
            // Niederschlag (mm)
            $table->float('precipitation_mean')->nullable(); // Normalniederschlag
            
            // Sonnenschein (Stunden)
            $table->float('sunshine_hours_mean')->nullable();
            
            // Referenzzeitraum
            $table->year('reference_period_start')->default(1991);
            $table->year('reference_period_end')->default(2020);
            
            // Metadaten
            $table->timestamps();
            $table->foreign('station_id')->references('id')->on('stations')->onDelete('cascade');
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
