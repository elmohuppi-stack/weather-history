<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Measurement extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'measurements';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'station_id',
        'date',
        'temp_max',
        'temp_min',
        'temp_mean',
        'precipitation',
        'sunshine',
        'snow_depth',
        'quality_flags',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
        'temp_max' => 'decimal:1',
        'temp_min' => 'decimal:1',
        'temp_mean' => 'decimal:1',
        'precipitation' => 'decimal:1',
        'sunshine' => 'decimal:1',
        'snow_depth' => 'decimal:1',
    ];

    /**
     * Get the station that owns the measurement.
     */
    public function station()
    {
        return $this->belongsTo(Station::class, 'station_id', 'id');
    }

    /**
     * Scope a query to only include measurements for a specific station.
     */
    public function scopeForStation($query, $stationId)
    {
        return $query->where('station_id', $stationId);
    }

    /**
     * Scope a query to only include measurements within a date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope a query to only include measurements with temperature data.
     */
    public function scopeHasTemperature($query)
    {
        return $query->whereNotNull('temp_mean');
    }

    /**
     * Scope a query to only include measurements with precipitation data.
     */
    public function scopeHasPrecipitation($query)
    {
        return $query->whereNotNull('precipitation');
    }

    /**
     * Get the formatted temperature with unit.
     */
    public function getFormattedTempMeanAttribute(): ?string
    {
        return $this->temp_mean ? "{$this->temp_mean} °C" : null;
    }

    /**
     * Get the formatted precipitation with unit.
     */
    public function getFormattedPrecipitationAttribute(): ?string
    {
        return $this->precipitation ? "{$this->precipitation} mm" : null;
    }

    /**
     * Get the formatted sunshine with unit.
     */
    public function getFormattedSunshineAttribute(): ?string
    {
        return $this->sunshine ? "{$this->sunshine} h" : null;
    }

    /**
     * Get the formatted snow depth with unit.
     */
    public function getFormattedSnowDepthAttribute(): ?string
    {
        return $this->snow_depth ? "{$this->snow_depth} cm" : null;
    }

    /**
     * Get the year from the date.
     */
    public function getYearAttribute(): int
    {
        return (int) date('Y', strtotime($this->date));
    }

    /**
     * Get the month from the date.
     */
    public function getMonthAttribute(): int
    {
        return (int) date('m', strtotime($this->date));
    }
}