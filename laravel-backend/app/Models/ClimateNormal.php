<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClimateNormal extends Model
{
    protected $table = 'climate_normals';
    
    protected $fillable = [
        'station_id',
        'month',
        'temp_mean',
        'temp_max_mean',
        'temp_min_mean',
        'precipitation_mean',
        'sunshine_hours_mean',
        'reference_period_start',
        'reference_period_end',
    ];
    
    protected $casts = [
        'month' => 'integer',
        'temp_mean' => 'float',
        'temp_max_mean' => 'float',
        'temp_min_mean' => 'float',
        'precipitation_mean' => 'float',
        'sunshine_hours_mean' => 'float',
        'reference_period_start' => 'integer',
        'reference_period_end' => 'integer',
    ];
    
    // Beziehung zur Station
    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class, 'station_id', 'id');
    }
    
    /**
     * Überprüfe, ob dies eine Jahresnormal ist (month=0) oder eine Monatsnormal
     */
    public function isYearlyNormal(): bool
    {
        return $this->month === 0;
    }
    
    /**
     * Rufe den Monatsnamen ab (z.B. "Januar")
     */
    public function getMonthNameAttribute(): ?string
    {
        if ($this->isYearlyNormal()) {
            return 'Jahresmittel';
        }
        
        $months = [
            1 => 'Januar', 2 => 'Februar', 3 => 'März',
            4 => 'April', 5 => 'Mai', 6 => 'Juni',
            7 => 'Juli', 8 => 'August', 9 => 'September',
            10 => 'Oktober', 11 => 'November', 12 => 'Dezember',
        ];
        return $months[$this->month] ?? null;
    }
}
