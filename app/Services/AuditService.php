<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditService
{
    /**
     * Log an audit event
     *
     * @param string $eventType
     * @param Model $auditable
     * @param string|null $description
     * @param array|null $oldValues
     * @param array|null $newValues
     * @param int|null $userId
     * @param array $metadata
     * @return AuditLog
     */
    public function log(
        string $eventType,
        Model $auditable,
        ?string $description = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?int $userId = null,
        array $metadata = []
    ): AuditLog {
        return AuditLog::create([
            'user_id' => $userId ?? Auth::id(),
            'auditable_type' => get_class($auditable),
            'auditable_id' => $auditable->id,
            'event_type' => $eventType,
            'event_description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Log document upload
     */
    public function logDocumentUpload(Model $document): AuditLog
    {
        return $this->log(
            'DOCUMENT_UPLOADED',
            $document,
            "Document '{$document->title}' uploaded",
            null,
            [
                'title' => $document->title,
                'file_size' => $document->file_size,
                'page_count' => $document->page_count,
            ]
        );
    }

    /**
     * Log document signed
     */
    public function logDocumentSigned(Model $document, int $signatureCount): AuditLog
    {
        return $this->log(
            'DOCUMENT_SIGNED',
            $document,
            "Document signed with {$signatureCount} signature(s)",
            ['status' => 'draft'],
            ['status' => 'signed']
        );
    }

    /**
     * Log signature created
     */
    public function logSignatureCreated(Model $signature): AuditLog
    {
        return $this->log(
            'SIGNATURE_CREATED',
            $signature,
            'Digital signature created',
            null,
            [
                'document_id' => $signature->document_id,
                'signed_at' => $signature->signed_at,
            ]
        );
    }

    /**
     * Log signature verified
     */
    public function logSignatureVerified(Model $signature, array $metadata = []): AuditLog
    {
        return $this->log(
            'SIGNATURE_VERIFIED',
            $signature,
            'Signature verification performed',
            null,
            $metadata,
            null // No user for public verification
        );
    }

    /**
     * Log signature area created
     */
    public function logSignatureAreaCreated(Model $area): AuditLog
    {
        return $this->log(
            'SIGNATURE_AREA_CREATED',
            $area,
            "Signature area created on page {$area->page_number}",
            null,
            [
                'page' => $area->page_number,
                'coordinates' => $area->coordinates,
            ]
        );
    }

    /**
     * Log document download
     */
    public function logDocumentDownload(Model $document): AuditLog
    {
        return $this->log(
            'DOCUMENT_DOWNLOADED',
            $document,
            "Document '{$document->title}' downloaded"
        );
    }

    /**
     * Get audit logs for a model
     */
    public function getLogsFor(Model $model, int $limit = 50)
    {
        return AuditLog::where('auditable_type', get_class($model))
            ->where('auditable_id', $model->id)
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get user activity
     */
    public function getUserActivity(int $userId, int $days = 30)
    {
        return AuditLog::where('user_id', $userId)
            ->where('created_at', '>=', now()->subDays($days))
            ->latest('created_at')
            ->get();
    }
}
