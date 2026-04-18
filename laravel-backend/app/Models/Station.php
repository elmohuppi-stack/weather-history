<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Station extends Model
{
    use HasFactory;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'name',
        'location',
        'elevation',
        'start_year',
        'measurement_count',
        'state',
        'latest_date',
        'active',
        'lat',
        'lon',
        'description',
        'dwd_url',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'elevation' => 'integer',
        'start_year' => 'integer',
        'measurement_count' => 'integer',
        'active' => 'boolean',
        'lat' => 'decimal:6',
        'lon' => 'decimal:6',
        'latest_date' => 'date',
    ];

    /**
     * Get the measurements for the station.
     */
    public function measurements()
    {
        return $this->hasMany(Measurement::class, 'station_id', 'id');
    }

    /**
     * Scope a query to only include active stations.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope a query to only include stations in a specific state.
     */
    public function scopeInState($query, $state)
    {
        return $query->where('state', $state);
    }

    /**
     * Get the formatted elevation with unit.
     */
    public function getFormattedElevationAttribute(): string
    {
        return $this->elevation ? "{$this->elevation} m" : 'N/A';
    }

    /**
     * Get the data period as a string.
     */
    public function getDataPeriodAttribute(): string
    {
        return "{$this->start_year}–" . ($this->latest_date ? date('Y', strtotime($this->latest_date)) : 'present');
    }

    /**
     * Get the coordinates as an array.
     */
    public function getCoordinatesAttribute(): array
    {
        return [
            'lat' => (float) $this->lat,
            'lon' => (float) $this->lon,
        ];
    }
}