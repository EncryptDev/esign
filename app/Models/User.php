<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'uuid',
        'name',
        'email',
        'password',
        'job_title',
        'department',
        'company_name',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (empty($user->uuid)) {
                $user->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get all documents owned by this user
     */
    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Get all signatures made by this user
     */
    public function signatures()
    {
        return $this->hasMany(Signature::class);
    }

    /**
     * Get all barcode tokens associated with this user
     */
    public function barcodeTokens()
    {
        return $this->hasMany(BarcodeToken::class);
    }

    /**
     * Get audit logs for this user
     */
    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Get document versions created by this user
     */
    public function documentVersions()
    {
        return $this->hasMany(DocumentVersion::class, 'created_by');
    }

    /**
     * Get full display name with job title
     */
    public function getFullDisplayNameAttribute(): string
    {
        $parts = [$this->name];

        if ($this->job_title) {
            $parts[] = $this->job_title;
        }

        return implode(' - ', $parts);
    }

    /**
     * Check if user has any signed documents
     */
    public function hasSignedDocuments(): bool
    {
        return $this->documents()
            ->whereIn('status', ['signed', 'final'])
            ->exists();
    }

    /**
     * Get route key name for route model binding
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
