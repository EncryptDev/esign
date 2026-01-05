<?php

namespace App\Http\Controllers;

use App\Models\BarcodeToken;
use App\Services\BarcodeService;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VerificationController extends Controller
{
    private BarcodeService $barcodeService;
    private AuditService $auditService;

    public function __construct(
        BarcodeService $barcodeService,
        AuditService $auditService
    ) {
        $this->barcodeService = $barcodeService;
        $this->auditService = $auditService;
    }

    /**
     * Show signature verification page
     * Public route: /verify/{token}
     *
     * @param Request $request
     * @param string $encodedToken Base64 encoded encrypted token
     * @return \Illuminate\View\View
     */
    public function show(Request $request, string $encodedToken)
    {
        try {
            // Decode token
            $encryptedToken = base64_decode($encodedToken);

            // Validate and decrypt token
            $payload = $this->barcodeService->validateToken($encryptedToken);

            if (!$payload) {
                return view('verification.invalid', [
                    'error' => 'Invalid or corrupted token'
                ]);
            }

            // Find barcode token record
            $tokenHash = hash('sha256', $encryptedToken);
            $barcodeToken = BarcodeToken::with([
                    'signature.user',
                    'signature.document',
                    'signature.signatureArea'
                ])
                ->where('token', $tokenHash)
                ->first();

            if (!$barcodeToken) {
                return view('verification.invalid', [
                    'error' => 'Token not found in system'
                ]);
            }

            // Check if token is still valid
            if (!$barcodeToken->is_valid) {
                return view('verification.revoked', [
                    'barcodeToken' => $barcodeToken,
                    'signature' => $barcodeToken->signature,
                    'document' => $barcodeToken->signature->document,
                    'signer' => $barcodeToken->signature->user,
                ]);
            }

            // Check expiration
            if ($barcodeToken->expires_at && $barcodeToken->expires_at->isPast()) {
                return view('verification.expired', [
                    'barcodeToken' => $barcodeToken,
                    'signature' => $barcodeToken->signature,
                    'document' => $barcodeToken->signature->document,
                    'signer' => $barcodeToken->signature->user,
                ]);
            }

            // Update verification tracking
            $this->trackVerification($barcodeToken, $request);

            // Prepare verification data
            $verificationData = $this->prepareVerificationData($barcodeToken);

            // Return verification view
            return view('verification.show', $verificationData);

        } catch (\Exception $e) {
            \Log::error('Verification error: ' . $e->getMessage(), [
                'token' => $encodedToken,
                'trace' => $e->getTraceAsString()
            ]);

            return view('verification.error', [
                'error' => 'An error occurred during verification'
            ]);
        }
    }

    /**
     * Track verification attempt
     *
     * @param BarcodeToken $barcodeToken
     * @param Request $request
     * @return void
     */
    private function trackVerification(BarcodeToken $barcodeToken, Request $request): void
    {
        DB::transaction(function () use ($barcodeToken, $request) {
            // Update verification count and timestamp
            $barcodeToken->increment('verified_count');
            $barcodeToken->update(['last_verified_at' => now()]);

            // Create audit log
            $this->auditService->log(
                'SIGNATURE_VERIFIED',
                $barcodeToken->signature,
                'Signature verification performed',
                null,
                [
                    'verified_count' => $barcodeToken->verified_count,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ],
                null // No authenticated user for public verification
            );
        });
    }

    /**
     * Prepare data for verification view
     *
     * @param BarcodeToken $barcodeToken
     * @return array
     */
    private function prepareVerificationData(BarcodeToken $barcodeToken): array
    {
        $signature = $barcodeToken->signature;
        $document = $signature ? $signature->document : null;
        $signer = $barcodeToken->user;

        $data = [
            // Token information
            'barcodeToken' => $barcodeToken,
            'tokenHash' => substr(hash('sha256', $barcodeToken->token), 0, 16),

            // Signer information (always available)
            'signer' => $signer,
            'signerName' => $signer->name,
            'signerJobTitle' => $signer->job_title,
            'signerDepartment' => $signer->department,
            'signerCompany' => $signer->company_name,

            // Verification metadata
            'verificationCount' => $barcodeToken->verified_count,
            'lastVerified' => $barcodeToken->last_verified_at,
            'expiresAt' => $barcodeToken->expires_at,
            'isValid' => $barcodeToken->is_valid,

            // Status badges
            'validityBadge' => $this->getValidityBadge($barcodeToken->is_valid),
        ];

        // Add document-specific data if exists (for document signatures)
        if ($signature && $document) {
            $data['signature'] = $signature;
            $data['signedAt'] = $signature->signed_at;
            $data['signatureHash'] = substr($signature->signature_hash, 0, 16);
            $data['document'] = $document;
            $data['documentTitle'] = $document->title;
            $data['documentPurpose'] = $document->purpose;
            $data['documentStatus'] = $document->status;
            $data['documentUuid'] = $document->uuid;
            $data['statusBadge'] = $this->getStatusBadge($document->status);
        } else {
            // Standalone QR code (no document)
            $data['signature'] = null;
            $data['document'] = null;
            $data['signedAt'] = $barcodeToken->created_at;
        }

        return $data;
    }

    /**
     * Get status badge configuration
     *
     * @param string $status
     * @return array
     */
    private function getStatusBadge(string $status): array
    {
        $badges = [
            'draft' => ['text' => 'Draft', 'color' => 'gray'],
            'signed' => ['text' => 'Signed', 'color' => 'blue'],
            'final' => ['text' => 'Final', 'color' => 'green'],
            'revoked' => ['text' => 'Revoked', 'color' => 'red'],
        ];

        return $badges[$status] ?? ['text' => 'Unknown', 'color' => 'gray'];
    }

    /**
     * Get validity badge configuration
     *
     * @param bool $isValid
     * @return array
     */
    private function getValidityBadge(bool $isValid): array
    {
        return $isValid
            ? ['text' => 'Valid', 'color' => 'green', 'icon' => 'check-circle']
            : ['text' => 'Invalid', 'color' => 'red', 'icon' => 'x-circle'];
    }

    /**
     * Download original document (optional feature)
     * This could be restricted or removed based on requirements
     *
     * @param string $encodedToken
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadDocument(string $encodedToken)
    {
        try {
            $encryptedToken = base64_decode($encodedToken);
            $payload = $this->barcodeService->validateToken($encryptedToken);

            if (!$payload) {
                abort(404, 'Invalid token');
            }

            $tokenHash = hash('sha256', $encryptedToken);
            $barcodeToken = BarcodeToken::with('signature.document')
                ->where('token', $tokenHash)
                ->where('is_valid', true)
                ->firstOrFail();

            $document = $barcodeToken->signature->document;

            // Get signed version
            $signedVersion = $document->versions()
                ->where('version_type', 'signed')
                ->latest()
                ->first();

            if (!$signedVersion) {
                abort(404, 'Signed document not found');
            }

            // Audit log
            $this->auditService->log(
                'DOCUMENT_DOWNLOADED_PUBLIC',
                $document,
                'Signed document downloaded via verification',
                null,
                ['token' => substr($tokenHash, 0, 16)]
            );

            return response()->download(
                \Storage::path($signedVersion->storage_path),
                $document->title . '_signed.pdf'
            );

        } catch (\Exception $e) {
            abort(404, 'Document not found');
        }
    }
}
