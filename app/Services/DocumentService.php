<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentService
{
    private PdfService $pdfService;
    private AuditService $auditService;

    public function __construct(PdfService $pdfService, AuditService $auditService)
    {
        $this->pdfService = $pdfService;
        $this->auditService = $auditService;
    }

    /**
     * Upload a new document
     *
     * @param User $user
     * @param UploadedFile $file
     * @param string $title
     * @param string|null $description
     * @param string|null $purpose
     * @param array $metadata
     * @return Document
     */
    public function uploadDocument(
        User $user,
        UploadedFile $file,
        string $title,
        ?string $description = null,
        ?string $purpose = null,
        array $metadata = []
    ): Document {
        // Generate unique paths
        $documentUuid = Str::uuid();
        $storagePath = "documents/{$user->uuid}/{$documentUuid}/original.pdf";

        // Store file
        $file->storeAs(
            dirname($storagePath),
            'original.pdf',
            'local'
        );

        // Create document record
        $document = Document::create([
            'uuid' => $documentUuid,
            'user_id' => $user->id,
            'title' => $title,
            'description' => $description,
            'purpose' => $purpose,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'storage_path' => $storagePath,
            'status' => 'draft',
            'page_count' => $metadata['page_count'] ?? 1,
            'checksum' => $metadata['checksum'] ?? hash_file('sha256', $file->getRealPath()),
        ]);

        // Create original version
        DocumentVersion::create([
            'uuid' => Str::uuid(),
            'document_id' => $document->id,
            'version_number' => 1,
            'version_type' => 'original',
            'storage_path' => $storagePath,
            'file_size' => $file->getSize(),
            'checksum' => $document->checksum,
            'metadata' => $metadata,
            'created_by' => $user->id,
        ]);

        // Audit log
        $this->auditService->logDocumentUpload($document);

        return $document;
    }

    /**
     * Get user's documents
     *
     * @param User $user
     * @param string|null $status
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserDocuments(User $user, ?string $status = null)
    {
        $query = Document::where('user_id', $user->id)
            ->with(['signatureAreas', 'signatures'])
            ->latest();

        if ($status) {
            $query->where('status', $status);
        }

        return $query->get();
    }

    /**
     * Get document by UUID
     *
     * @param string $uuid
     * @param User $user
     * @return Document
     */
    public function getDocumentByUuid(string $uuid, User $user): Document
    {
        return Document::where('uuid', $uuid)
            ->where('user_id', $user->id)
            ->with(['signatureAreas', 'signatures', 'versions'])
            ->firstOrFail();
    }

    /**
     * Delete document
     *
     * @param Document $document
     * @return bool
     */
    public function deleteDocument(Document $document): bool
    {
        if (!$document->canBeDeleted()) {
            throw new \Exception('Cannot delete document with status: ' . $document->status);
        }

        // Delete files from storage
        $this->deleteDocumentFiles($document);

        // Soft delete document
        $document->delete();

        // Audit log
        $this->auditService->log(
            'DOCUMENT_DELETED',
            $document,
            "Document '{$document->title}' deleted"
        );

        return true;
    }

    /**
     * Delete document files from storage
     *
     * @param Document $document
     * @return void
     */
    private function deleteDocumentFiles(Document $document): void
    {
        // Delete all versions
        foreach ($document->versions as $version) {
            if (Storage::exists($version->storage_path)) {
                Storage::delete($version->storage_path);
            }
        }

        // Delete barcode images
        foreach ($document->barcodeTokens as $token) {
            // Extract barcode path from metadata if exists
            if (isset($token->metadata['barcode_path'])) {
                if (Storage::exists($token->metadata['barcode_path'])) {
                    Storage::delete($token->metadata['barcode_path']);
                }
            }
        }

        // Delete document directory
        $documentDir = dirname($document->storage_path);
        if (Storage::exists($documentDir)) {
            Storage::deleteDirectory($documentDir);
        }
    }

    /**
     * Update document metadata
     *
     * @param Document $document
     * @param array $data
     * @return Document
     */
    public function updateDocument(Document $document, array $data): Document
    {
        if ($document->isImmutable()) {
            throw new \Exception('Cannot update immutable document');
        }

        $oldValues = $document->only(['title', 'description', 'purpose']);

        $document->update($data);

        $this->auditService->log(
            'DOCUMENT_UPDATED',
            $document,
            'Document information updated',
            $oldValues,
            $document->only(['title', 'description', 'purpose'])
        );

        return $document;
    }

    /**
     * Get document statistics for user
     *
     * @param User $user
     * @return array
     */
    public function getUserDocumentStatistics(User $user): array
    {
        $documents = Document::where('user_id', $user->id);

        return [
            'total' => $documents->count(),
            'draft' => (clone $documents)->where('status', 'draft')->count(),
            'signed' => (clone $documents)->where('status', 'signed')->count(),
            'final' => (clone $documents)->where('status', 'final')->count(),
            'revoked' => (clone $documents)->where('status', 'revoked')->count(),
            'total_signatures' => $user->signatures()->count(),
            'recent_uploads' => (clone $documents)->where('created_at', '>=', now()->subDays(30))->count(),
        ];
    }

    /**
     * Download document file
     *
     * @param Document $document
     * @param string $versionType
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadDocument(Document $document, string $versionType = 'original')
    {
        $version = $document->versions()
            ->where('version_type', $versionType)
            ->latest('version_number')
            ->firstOrFail();

        if (!Storage::exists($version->storage_path)) {
            throw new \Exception('Document file not found');
        }

        // Audit log
        $this->auditService->logDocumentDownload($document);

        $filename = $document->title . '_' . $versionType . '.pdf';

        return response()->download(
            Storage::path($version->storage_path),
            $filename
        );
    }

    /**
     * Check if user can access document
     *
     * @param Document $document
     * @param User $user
     * @return bool
     */
    public function canUserAccessDocument(Document $document, User $user): bool
    {
        return $document->user_id === $user->id;
    }
}
