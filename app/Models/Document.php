<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Document extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'user_id',
        'title',
        'description',
        'purpose',
        'original_filename',
        'mime_type',
        'file_size',
        'storage_path',
        'status',
        'page_count',
        'checksum',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'page_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($document) {
            if (empty($document->uuid)) {
                $document->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the user who owns the document
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all versions of this document
     */
    public function versions()
    {
        return $this->hasMany(DocumentVersion::class);
    }

    /**
     * Get the original version
     */
    public function originalVersion()
    {
        return $this->hasOne(DocumentVersion::class)
            ->where('version_type', 'original')
            ->oldest();
    }

    /**
     * Get the latest signed version
     */
    public function signedVersion()
    {
        return $this->hasOne(DocumentVersion::class)
            ->where('version_type', 'signed')
            ->latest('version_number');
    }

    /**
     * Get all signature areas
     */
    public function signatureAreas()
    {
        return $this->hasMany(SignatureArea::class);
    }

    /**
     * Get unsigned signature areas
     */
    public function unsignedAreas()
    {
        return $this->hasMany(SignatureArea::class)
            ->where('is_signed', false);
    }

    /**
     * Get all signatures
     */
    public function signatures()
    {
        return $this->hasMany(Signature::class);
    }

    /**
     * Get all barcode tokens
     */
    public function barcodeTokens()
    {
        return $this->hasMany(BarcodeToken::class);
    }

    /**
     * Get audit logs for this document
     */
    public function auditLogs()
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }

    /**
     * Scope: Only draft documents
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope: Only signed documents
     */
    public function scopeSigned($query)
    {
        return $query->where('status', 'signed');
    }

    /**
     * Scope: Only final documents
     */
    public function scopeFinal($query)
    {
        return $query->where('status', 'final');
    }

    /**
     * Scope: Documents owned by specific user
     */
    public function scopeOwnedBy($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Check if document can be edited
     */
    public function canBeEdited(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if document can be signed
     */
    public function canBeSigned(): bool
    {
        return $this->status === 'draft'
            && $this->signatureAreas()->exists()
            && $this->unsignedAreas()->exists();
    }

    /**
     * Check if document can be deleted
     */
    public function canBeDeleted(): bool
    {
        return $this->status === 'draft' || $this->status === 'revoked';
    }

    /**
     * Check if document is immutable
     */
    public function isImmutable(): bool
    {
        return in_array($this->status, ['signed', 'final']);
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
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft' => 'gray',
            'signed' => 'blue',
            'final' => 'green',
            'revoked' => 'red',
            default => 'gray',
        };
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return ucfirst($this->status);
    }

    /**
     * Get route key name for route model binding
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
