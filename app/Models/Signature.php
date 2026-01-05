<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Signature extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'document_id',
        'signature_area_id',
        'user_id',
        'signed_at',
        'ip_address',
        'user_agent',
        'signature_hash',
        'metadata',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($signature) {
            if (empty($signature->uuid)) {
                $signature->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the document this signature belongs to
     */
    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Get the signature area
     */
    public function signatureArea()
    {
        return $this->belongsTo(SignatureArea::class);
    }

    /**
     * Get the user who signed
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the barcode token
     */
    public function barcodeToken()
    {
        return $this->hasOne(BarcodeToken::class);
    }

    /**
     * Get audit logs for this signature
     */
    public function auditLogs()
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }

    /**
     * Scope: Signatures by specific user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Signatures on specific document
     */
    public function scopeOnDocument($query, $documentId)
    {
        return $query->where('document_id', $documentId);
    }

    /**
     * Scope: Recent signatures
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('signed_at', '>=', now()->subDays($days));
    }

    /**
     * Check if signature is valid
     */
    public function isValid(): bool
    {
        return $this->barcodeToken && $this->barcodeToken->is_valid;
    }

    /**
     * Get signer name
     */
    public function getSignerNameAttribute(): string
    {
        return $this->user->name;
    }

    /**
     * Get signer job title
     */
    public function getSignerJobTitleAttribute(): ?string
    {
        return $this->user->job_title;
    }

    /**
     * Get formatted signed date
     */
    public function getSignedAtFormattedAttribute(): string
    {
        return $this->signed_at->format('F d, Y \a\t h:i A');
    }

    /**
     * Get signing location (based on IP)
     */
    public function getSigningLocationAttribute(): string
    {
        // In production, you might want to use a GeoIP service
        return $this->ip_address;
    }

    /**
     * Get short signature hash
     */
    public function getShortHashAttribute(): string
    {
        return substr($this->signature_hash, 0, 16) . '...';
    }

    /**
     * Get route key name
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
