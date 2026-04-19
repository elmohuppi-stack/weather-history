<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class YearlyAggregate extends Model
{
    protected $table = 'yearly_aggregates';
    
    protected $fillable = [
        'station_id',
        'year',
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
}
