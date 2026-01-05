<?php

namespace App\Services;

use App\Models\Document;
use App\Models\Signature;
use App\Models\SignatureArea;
use App\Models\BarcodeToken;
use App\Models\DocumentVersion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SignatureService
{
    private BarcodeService $barcodeService;
    private PdfService $pdfService;
    private AuditService $auditService;

    public function __construct(
        BarcodeService $barcodeService,
        PdfService $pdfService,
        AuditService $auditService
    ) {
        $this->barcodeService = $barcodeService;
        $this->pdfService = $pdfService;
        $this->auditService = $auditService;
    }

    /**
     * Sign a document with digital stamps
     *
     * @param Document $document
     * @return Signature[]
     * @throws \Exception
     */
    public function signDocument(Document $document): array
    {
        // Validation
        $this->validateDocumentForSigning($document);

        return DB::transaction(function () use ($document) {
            $user = Auth::user();
            $signatureAreas = $document->signatureAreas()
                ->where('is_signed', false)
                ->get();

            if ($signatureAreas->isEmpty()) {
                throw new \Exception('No unsigned signature areas found');
            }

            $signatures = [];
            $stampData = [];

            // Create signatures and barcodes for each area
            foreach ($signatureAreas as $area) {
                // Create signature record
                $signature = $this->createSignatureRecord($document, $area, $user);

                // Generate secure token
                $encryptedToken = $this->barcodeService->generateSecureToken(
                    $document->id,
                    $user->id,
                    $signature->id
                );

                // Generate verification URL
                $verificationUrl = $this->barcodeService->generateVerificationUrl($encryptedToken);

                // Generate QR code with company logo
                $barcodePath = $this->barcodeService->generateQRCodeWithLogo(
                    $verificationUrl,
                    config('company.logo.path')
                );

                // Create barcode token record
                $barcodeToken = $this->createBarcodeTokenRecord(
                    $signature,
                    $encryptedToken,
                    $verificationUrl,
                    $barcodePath
                );

                // Mark signature area as signed
                $area->update(['is_signed' => true]);

                // Collect stamp data for PDF processing
                $stampData[] = [
                    'signature_area' => $area,
                    'signature' => $signature,
                    'barcode_token' => $barcodeToken,
                    'barcode_path' => $barcodePath,
                ];

                $signatures[] = $signature;

                // Audit log
                $this->auditService->log(
                    'SIGNATURE_CREATED',
                    $signature,
                    'Signature created for document',
                    null,
                    $signature->toArray()
                );
            }

            // Stamp PDF with all barcodes
            $signedPdfPath = $this->pdfService->stampPdfWithBarcodes($document, $stampData);

            // Create new document version
            $this->createSignedVersion($document, $signedPdfPath);

            // Update document status
            $document->update(['status' => 'signed']);

            // Audit log for document
            $this->auditService->log(
                'DOCUMENT_SIGNED',
                $document,
                'Document signed with ' . count($signatures) . ' signature(s)',
                ['status' => 'draft'],
                ['status' => 'signed']
            );

            return $signatures;
        });
    }

    /**
     * Validate document can be signed
     *
     * @param Document $document
     * @throws \Exception
     */
    private function validateDocumentForSigning(Document $document): void
    {
        // Check ownership
        if ($document->user_id !== Auth::id()) {
            throw new \Exception('You do not have permission to sign this document');
        }

        // Check status
        if ($document->status !== 'draft') {
            throw new \Exception('Only draft documents can be signed');
        }

        // Check signature areas exist
        if ($document->signatureAreas()->count() === 0) {
            throw new \Exception('No signature areas defined for this document');
        }

        // Check at least one unsigned area exists
        if ($document->signatureAreas()->where('is_signed', false)->count() === 0) {
            throw new \Exception('All signature areas are already signed');
        }
    }

    /**
     * Create signature database record
     *
     * @param Document $document
     * @param SignatureArea $area
     * @param \App\Models\User $user
     * @return Signature
     */
    private function createSignatureRecord(
        Document $document,
        SignatureArea $area,
        $user
    ): Signature {
        return Signature::create([
            'uuid' => Str::uuid(),
            'document_id' => $document->id,
            'signature_area_id' => $area->id,
            'user_id' => $user->id,
            'signed_at' => now(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'signature_hash' => hash('sha256', json_encode([
                'document_id' => $document->id,
                'user_id' => $user->id,
                'timestamp' => now()->timestamp,
            ])),
            'metadata' => [
                'document_title' => $document->title,
                'signer_name' => $user->name,
                'signer_job_title' => $user->job_title,
            ],
        ]);
    }

    /**
     * Create barcode token record
     *
     * @param Signature $signature
     * @param string $encryptedToken
     * @param string $verificationUrl
     * @param string $barcodePath
     * @return BarcodeToken
     */
    private function createBarcodeTokenRecord(
        Signature $signature,
        string $encryptedToken,
        string $verificationUrl,
        string $barcodePath
    ): BarcodeToken {
        return BarcodeToken::create([
            'token' => hash('sha256', $encryptedToken),
            'signature_id' => $signature->id,
            'document_id' => $signature->document_id,
            'user_id' => $signature->user_id,
            'barcode_type' => 'qr_code',
            'barcode_data' => $encryptedToken,
            'verification_url' => $verificationUrl,
            'is_valid' => true,
            'verified_count' => 0,
            'expires_at' => now()->addYears(10), // 10 year validity
        ]);
    }

    /**
     * Create signed document version
     *
     * @param Document $document
     * @param string $signedPdfPath
     * @return DocumentVersion
     */
    private function createSignedVersion(Document $document, string $signedPdfPath): DocumentVersion
    {
        $latestVersion = $document->versions()
            ->orderBy('version_number', 'desc')
            ->first();

        $newVersionNumber = $latestVersion ? $latestVersion->version_number + 1 : 2;

        $metadata = $this->pdfService->extractMetadata(
            \Storage::path($signedPdfPath)
        );

        return DocumentVersion::create([
            'uuid' => Str::uuid(),
            'document_id' => $document->id,
            'version_number' => $newVersionNumber,
            'version_type' => 'signed',
            'storage_path' => $signedPdfPath,
            'file_size' => $metadata['file_size'],
            'checksum' => $metadata['checksum'],
            'metadata' => $metadata,
            'created_by' => Auth::id(),
        ]);
    }

    /**
     * Finalize document (make it immutable)
     *
     * @param Document $document
     * @return Document
     */
    public function finalizeDocument(Document $document): Document
    {
        // Verify all signature areas are signed
        $unsignedAreas = $document->signatureAreas()
            ->where('is_signed', false)
            ->count();

        if ($unsignedAreas > 0) {
            throw new \Exception('Cannot finalize: ' . $unsignedAreas . ' unsigned area(s) remaining');
        }

        $document->update(['status' => 'final']);

        $this->auditService->log(
            'DOCUMENT_FINALIZED',
            $document,
            'Document finalized and locked',
            ['status' => 'signed'],
            ['status' => 'final']
        );

        return $document;
    }

    /**
     * Revoke a signature
     *
     * @param Signature $signature
     * @param string $reason
     * @return void
     */
    public function revokeSignature(Signature $signature, string $reason): void
    {
        DB::transaction(function () use ($signature, $reason) {
            // Invalidate barcode token
            $signature->barcodeToken->update(['is_valid' => false]);

            // Update document status
            $signature->document->update(['status' => 'revoked']);

            // Audit log
            $this->auditService->log(
                'SIGNATURE_REVOKED',
                $signature,
                'Signature revoked: ' . $reason,
                ['is_valid' => true],
                ['is_valid' => false]
            );
        });
    }
}
