<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonthlyAggregate extends Model
{
    protected $table = 'monthly_aggregates';
    
    protected $fillable = [
        'station_id',
        'year',
        'month',
        'temp_max_absolute',
        'temp_min_absolute',
        'temp_mean',
        'precipitation_sum',
        'sunshine_hours',
        'snow_depth_max',
        'frost_days',
        'summer_days',
        'rainy_days',
        'snowy_days',
        'records_count',
        'valid_records',
    ];
    
    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'temp_max_absolute' => 'float',
        'temp_min_absolute' => 'float',
        'temp_mean' => 'float',
        'precipitation_sum' => 'float',
        'sunshine_hours' => 'float',
        'snow_depth_max' => 'float',
        'frost_days' => 'integer',
        'summer_days' => 'integer',
        'rainy_days' => 'integer',
        'snowy_days' => 'integer',
        'records_count' => 'integer',
        'valid_records' => 'integer',
    ];
    
    // Beziehung zur Station
    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class, 'station_id', 'id');
    }
    
    /**
     * Erstelle einen Monatsnamen (z.B. "Januar 2024")
     */
    public function getMonthNameAttribute(): string
    {
        $months = [
            1 => 'Januar', 2 => 'Februar', 3 => 'März',
            4 => 'April', 5 => 'Mai', 6 => 'Juni',
            7 => 'Juli', 8 => 'August', 9 => 'September',
            10 => 'Oktober', 11 => 'November', 12 => 'Dezember',
        ];
        return "{$months[$this->month]} {$this->year}";
    }
}
