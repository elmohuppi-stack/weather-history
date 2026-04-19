<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'import_type',
        'station_id',
        'operation',
        'records_processed',
        'records_imported',
        'records_skipped',
        'records_failed',
        'success',
        'duration_seconds',
        'error_message',
        'parameters',
        'user_initiated',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'records_processed' => 'integer',
        'records_imported' => 'integer',
        'records_skipped' => 'integer',
        'records_failed' => 'integer',
        'success' => 'boolean',
        'duration_seconds' => 'float',
        'parameters' => 'array',
        'user_initiated' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Import type constants.
     */
    public const TYPE_HISTORICAL = 'historical';
    public const TYPE_RECENT = 'recent';
    public const TYPE_FULL = 'full';
    public const TYPE_STATION_ADD = 'station_add';
    public const TYPE_UPDATE = 'update';

    /**
     * Operation constants.
     */
    public const OPERATION_DOWNLOAD = 'download';
    public const OPERATION_PARSE = 'parse';
    public const OPERATION_IMPORT = 'import';
    public const OPERATION_UPDATE = 'update';
    public const OPERATION_VALIDATE = 'validate';

    /**
     * Get the station associated with the import log.
     */
    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class, 'station_id', 'id');
    }

    /**
     * Scope a query to only include successful imports.
     */
    public function scopeSuccessful($query)
    {
        return $query->where('success', true);
    }

    /**
     * Scope a query to only include failed imports.
     */
    public function scopeFailed($query)
    {
        return $query->where('success', false);
    }

    /**
     * Scope a query to only include imports of a specific type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('import_type', $type);
    }

    /**
     * Scope a query to only include imports for a specific station.
     */
    public function scopeForStation($query, $stationId)
    {
        return $query->where('station_id', $stationId);
    }

    /**
     * Scope a query to only include user-initiated imports.
     */
    public function scopeUserInitiated($query)
    {
        return $query->where('user_initiated', true);
    }

    /**
     * Scope a query to only include automated imports.
     */
    public function scopeAutomated($query)
    {
        return $query->where('user_initiated', false);
    }

    /**
     * Get the formatted duration.
     */
    public function getFormattedDurationAttribute(): string
    {
        if (!$this->duration_seconds) {
            return 'N/A';
        }

        if ($this->duration_seconds < 60) {
            return sprintf('%.1f s', $this->duration_seconds);
        }

        $minutes = floor($this->duration_seconds / 60);
        $seconds = $this->duration_seconds % 60;

        if ($minutes < 60) {
            return sprintf('%d m %d s', $minutes, $seconds);
        }

        $hours = floor($minutes / 60);
        $minutes = $minutes % 60;

        return sprintf('%d h %d m %d s', $hours, $minutes, $seconds);
    }

    /**
     * Get the success status as a string.
     */
    public function getStatusAttribute(): string
    {
        return $this->success ? 'success' : 'failed';
    }

    /**
     * Get the status color for UI.
     */
    public function getStatusColorAttribute(): string
    {
        return $this->success ? 'green' : 'red';
    }

    /**
     * Get the import type as a human-readable string.
     */
    public function getImportTypeLabelAttribute(): string
    {
        return match($this->import_type) {
            self::TYPE_HISTORICAL => 'Historical Data',
            self::TYPE_RECENT => 'Recent Data',
            self::TYPE_FULL => 'Full Import',
            self::TYPE_STATION_ADD => 'Add Station',
            self::TYPE_UPDATE => 'Update',
            default => ucfirst($this->import_type),
        };
    }

    /**
     * Get the operation as a human-readable string.
     */
    public function getOperationLabelAttribute(): string
    {
        return match($this->operation) {
            self::OPERATION_DOWNLOAD => 'Download',
            self::OPERATION_PARSE => 'Parse',
            self::OPERATION_IMPORT => 'Import',
            self::OPERATION_UPDATE => 'Update',
            self::OPERATION_VALIDATE => 'Validate',
            default => ucfirst($this->operation),
        };
    }

    /**
     * Create a success log entry.
     */
    public static function logSuccess(string $importType, ?string $stationId, string $operation, array $stats = [], array $parameters = [], bool $userInitiated = false): self
    {
        return self::create([
            'import_type' => $importType,
            'station_id' => $stationId,
            'operation' => $operation,
            'records_processed' => $stats['processed'] ?? 0,
            'records_imported' => $stats['imported'] ?? 0,
            'records_skipped' => $stats['skipped'] ?? 0,
            'records_failed' => $stats['failed'] ?? 0,
            'success' => true,
            'duration_seconds' => $stats['duration'] ?? null,
            'parameters' => $parameters,
            'user_initiated' => $userInitiated,
        ]);
    }

    /**
     * Create a failure log entry.
     */
    public static function logFailure(string $importType, ?string $stationId, string $operation, string $errorMessage, array $stats = [], array $parameters = [], bool $userInitiated = false): self
    {
        return self::create([
            'import_type' => $importType,
            'station_id' => $stationId,
            'operation' => $operation,
            'records_processed' => $stats['processed'] ?? 0,
            'records_imported' => $stats['imported'] ?? 0,
            'records_skipped' => $stats['skipped'] ?? 0,
            'records_failed' => $stats['failed'] ?? 0,
            'success' => false,
            'duration_seconds' => $stats['duration'] ?? null,
            'error_message' => $errorMessage,
            'parameters' => $parameters,
            'user_initiated' => $userInitiated,
        ]);
    }
}
