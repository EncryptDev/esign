<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DocumentVersion extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'uuid',
        'document_id',
        'version_number',
        'version_type',
        'storage_path',
        'file_size',
        'checksum',
        'metadata',
        'created_by',
    ];

    protected $casts = [
        'version_number' => 'integer',
        'file_size' => 'integer',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($version) {
            if (empty($version->uuid)) {
                $version->uuid = (string) Str::uuid();
            }

            if (empty($version->created_at)) {
                $version->created_at = now();
            }
        });
    }

    /**
     * Get the document this version belongs to
     */
    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Get the user who created this version
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if this is the original version
     */
    public function isOriginal(): bool
    {
        return $this->version_type === 'original';
    }

    /**
     * Check if this is a signed version
     */
    public function isSigned(): bool
    {
        return $this->version_type === 'signed';
    }

    /**
     * Get file size in human readable format
     */
    public function getFileSizeHumanAttribute(): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }

    /**
     * Get version type label
     */
    public function getVersionTypeLabelAttribute(): string
    {
        return match($this->version_type) {
            'original' => 'Original',
            'signed' => 'Signed',
            'revised' => 'Revised',
            default => ucfirst($this->version_type),
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
