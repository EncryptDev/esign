<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AuditLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'uuid',
        'user_id',
        'auditable_type',
        'auditable_id',
        'event_type',
        'event_description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($log) {
            if (empty($log->uuid)) {
                $log->uuid = (string) Str::uuid();
            }

            if (empty($log->created_at)) {
                $log->created_at = now();
            }

            // Auto-capture request info if not provided
            if (empty($log->ip_address)) {
                $log->ip_address = request()->ip();
            }

            if (empty($log->user_agent)) {
                $log->user_agent = request()->userAgent();
            }
        });
    }

    /**
     * Get the user who performed the action
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the auditable model
     */
    public function auditable()
    {
        return $this->morphTo();
    }

    /**
     * Scope: Logs by specific user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Logs for specific event type
     */
    public function scopeEventType($query, string $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    /**
     * Scope: Logs within date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope: Recent logs
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Get event type label
     */
    public function getEventTypeLabelAttribute(): string
    {
        return str_replace('_', ' ', ucwords(strtolower($this->event_type), '_'));
    }

    /**
     * Get user name
     */
    public function getUserNameAttribute(): string
    {
        return $this->user ? $this->user->name : 'System';
    }

    /**
     * Get formatted date
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->created_at->format('M d, Y h:i A');
    }

    /**
     * Get changes summary
     */
    public function getChangesSummaryAttribute(): string
    {
        if (empty($this->old_values) && empty($this->new_values)) {
            return $this->event_description ?? 'No changes';
        }

        $changes = [];

        if ($this->old_values && $this->new_values) {
            foreach ($this->new_values as $key => $newValue) {
                $oldValue = $this->old_values[$key] ?? null;
                if ($oldValue !== $newValue) {
                    $changes[] = "{$key}: {$oldValue} → {$newValue}";
                }
            }
        }

        return empty($changes)
            ? ($this->event_description ?? 'No changes')
            : implode(', ', $changes);
    }

    /**
     * Get event icon
     */
    public function getEventIconAttribute(): string
    {
        return match(true) {
            str_contains($this->event_type, 'CREATE') => 'plus-circle',
            str_contains($this->event_type, 'UPDATE') => 'edit',
            str_contains($this->event_type, 'DELETE') => 'trash',
            str_contains($this->event_type, 'SIGN') => 'pen-tool',
            str_contains($this->event_type, 'VERIFY') => 'check-circle',
            str_contains($this->event_type, 'REVOKE') => 'x-circle',
            str_contains($this->event_type, 'DOWNLOAD') => 'download',
            str_contains($this->event_type, 'UPLOAD') => 'upload',
            default => 'activity',
        };
    }

    /**
     * Get event color
     */
    public function getEventColorAttribute(): string
    {
        return match(true) {
            str_contains($this->event_type, 'CREATE') => 'green',
            str_contains($this->event_type, 'UPDATE') => 'blue',
            str_contains($this->event_type, 'DELETE') => 'red',
            str_contains($this->event_type, 'SIGN') => 'purple',
            str_contains($this->event_type, 'VERIFY') => 'teal',
            str_contains($this->event_type, 'REVOKE') => 'red',
            default => 'gray',
        };
    }

    /**
     * Get route key name
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
