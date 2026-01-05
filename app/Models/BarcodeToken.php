<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarcodeToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'token',
        'signature_id',
        'document_id',
        'user_id',
        'barcode_type',
        'barcode_data',
        'verification_url',
        'is_valid',
        'verified_count',
        'last_verified_at',
        'expires_at',
        'metadata'
    ];

    protected $casts = [
        'is_valid' => 'boolean',
        'verified_count' => 'integer',
        'last_verified_at' => 'datetime',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'metadata' => 'array'
    ];

    /**
     * Get the signature this token belongs to
     */
    public function signature()
    {
        return $this->belongsTo(Signature::class);
    }

    /**
     * Get the document
     */
    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Get the user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: Only valid tokens
     */
    public function scopeValid($query)
    {
        return $query->where('is_valid', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Scope: Invalid tokens
     */
    public function scopeInvalid($query)
    {
        return $query->where('is_valid', false);
    }

    /**
     * Scope: Expired tokens
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    /**
     * Check if token is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if token is still valid
     */
    public function isStillValid(): bool
    {
        return $this->is_valid && !$this->isExpired();
    }

    /**
     * Revoke this token
     */
    public function revoke(): bool
    {
        return $this->update(['is_valid' => false]);
    }

    /**
     * Increment verification count
     */
    public function incrementVerificationCount(): void
    {
        $this->increment('verified_count');
        $this->update(['last_verified_at' => now()]);
    }

    /**
     * Get short token
     */
    public function getShortTokenAttribute(): string
    {
        return substr($this->token, 0, 16) . '...';
    }

    /**
     * Get verification status
     */
    public function getVerificationStatusAttribute(): string
    {
        if (!$this->is_valid) {
            return 'revoked';
        }

        if ($this->isExpired()) {
            return 'expired';
        }

        return 'valid';
    }

    /**
     * Get status color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->verification_status) {
            'valid' => 'green',
            'expired' => 'yellow',
            'revoked' => 'red',
            default => 'gray',
        };
    }

    /**
     * Get time until expiration
     */
    public function getTimeUntilExpirationAttribute(): ?string
    {
        if (!$this->expires_at) {
            return null;
        }

        if ($this->isExpired()) {
            return 'Expired ' . $this->expires_at->diffForHumans();
        }

        return 'Expires ' . $this->expires_at->diffForHumans();
    }
}
