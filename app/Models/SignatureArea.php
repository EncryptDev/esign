<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SignatureArea extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'document_id',
        'page_number',
        'position_x',
        'position_y',
        'width',
        'height',
        'label',
        'is_signed',
    ];

    protected $casts = [
        'page_number' => 'integer',
        'position_x' => 'decimal:2',
        'position_y' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'is_signed' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($area) {
            if (empty($area->uuid)) {
                $area->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the document this area belongs to
     */
    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Get the signature for this area (if signed)
     */
    public function signature()
    {
        return $this->hasOne(Signature::class);
    }

    /**
     * Get audit logs for this signature area
     */
    public function auditLogs()
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }

    /**
     * Scope: Only unsigned areas
     */
    public function scopeUnsigned($query)
    {
        return $query->where('is_signed', false);
    }

    /**
     * Scope: Only signed areas
     */
    public function scopeSigned($query)
    {
        return $query->where('is_signed', true);
    }

    /**
     * Scope: Areas on specific page
     */
    public function scopeOnPage($query, int $pageNumber)
    {
        return $query->where('page_number', $pageNumber);
    }

    /**
     * Check if this area can be deleted
     */
    public function canBeDeleted(): bool
    {
        return !$this->is_signed;
    }

    /**
     * Get coordinates as array
     */
    public function getCoordinatesAttribute(): array
    {
        return [
            'x' => (float) $this->position_x,
            'y' => (float) $this->position_y,
            'width' => (float) $this->width,
            'height' => (float) $this->height,
        ];
    }

    /**
     * Get area dimensions in pixels (assuming 72 DPI)
     */
    public function getDimensionsInPixelsAttribute(): array
    {
        $dpi = 72;

        return [
            'x' => round($this->position_x * $dpi / 25.4),
            'y' => round($this->position_y * $dpi / 25.4),
            'width' => round($this->width * $dpi / 25.4),
            'height' => round($this->height * $dpi / 25.4),
        ];
    }

    /**
     * Get display label
     */
    public function getDisplayLabelAttribute(): string
    {
        return $this->label ?: "Area on page {$this->page_number}";
    }

    /**
     * Get route key name
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
