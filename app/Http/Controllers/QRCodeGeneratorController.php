<?php

namespace App\Http\Controllers;

use App\Models\BarcodeToken;
use App\Models\User;
use App\Services\BarcodeService;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class QRCodeGeneratorController extends Controller
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
     * Show QR code generator form
     */
    public function index()
    {
        $user = Auth::user();

        // Get user's generated QR codes
        $qrCodes = BarcodeToken::where('user_id', $user->id)
            ->whereNull('document_id') // Standalone QR codes only
            ->latest()
            ->paginate(10);

        return view('qrcodes.index', compact('qrCodes'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        return view('qrcodes.create');
    }

    /**
     * Generate standalone QR code
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'purpose' => 'required|string|max:500',
            'valid_until' => 'nullable|date|after:today',
        ]);

        $user = Auth::user();

        try {
            // Generate secure token
            $encryptedToken = $this->barcodeService->generateSecureToken(
                0, // No document
                $user->id,
                0  // No signature
            );

            // Generate verification URL
            $verificationUrl = $this->barcodeService->generateVerificationUrl($encryptedToken);

            // Generate QR code with company logo
            $barcodePath = $this->barcodeService->generateQRCodeWithLogo(
                $verificationUrl,
                config('company.logo.path')
            );

            // Create barcode token record
            $barcodeToken = BarcodeToken::create([
                'token' => hash('sha256', $encryptedToken),
                'signature_id' => null,
                'document_id' => null,
                'user_id' => $user->id,
                'barcode_type' => 'qr_code',
                'barcode_data' => $encryptedToken,
                'verification_url' => $verificationUrl,
                'is_valid' => true,
                'verified_count' => 0,
                'expires_at' => $validated['valid_until'] ?? null,
                'metadata' => [
                    'title' => $validated['title'],
                    'description' => $validated['description'],
                    'purpose' => $validated['purpose'],
                    'barcode_path' => $barcodePath,
                    'created_by' => $user->name,
                    'job_title' => $user->job_title,
                    'company' => $user->company_name,
                ],
            ]);

            // Audit log
            $this->auditService->log(
                'QRCODE_GENERATED',
                $barcodeToken,
                'Standalone QR code generated: ' . $validated['title'],
                null,
                [
                    'title' => $validated['title'],
                    'purpose' => $validated['purpose'],
                ]
            );

            return redirect()
                ->route('qrcodes.show', $barcodeToken->id)
                ->with('success', 'QR Code generated successfully!');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to generate QR code: ' . $e->getMessage());
        }
    }

    /**
     * Show QR code details
     */
    public function show($id)
    {
        $user = Auth::user();

        $qrCode = BarcodeToken::where('id', $id)
            ->where('user_id', $user->id)
            ->whereNull('document_id')
            ->firstOrFail();

        return view('qrcodes.show', compact('qrCode'));
    }

    /**
     * Download QR code image
     */
 public function download($id)
{
    $user = Auth::user();

    $qrCode = BarcodeToken::where('id', $id)
        ->where('user_id', $user->id)
        ->whereNull('document_id')
        ->firstOrFail();

    $fullPath = $qrCode->metadata['barcode_path'] ?? null;

    if (!$fullPath) {
        return redirect()->back()->with('error', 'Path tidak ditemukan di metadata');
    }

    // Perbaikan: Ambil nama filenya saja dan arahkan ke disk public
    $fileNameOnly = basename($fullPath);
    $relativeDiskPath = $fileNameOnly; // Jika file ada di root 'public' folder pada disk 'public'

    // Gunakan disk yang sesuai (biasanya 'public')
    if (!Storage::disk('public')->exists($relativeDiskPath)) {
        // Coba log untuk debugging jika gagal
        \Log::error("File tidak ditemukan di: " . $relativeDiskPath);
        return redirect()->back()->with('error', 'File fisik tidak ditemukan di storage');
    }

    $downloadName = Str::slug($qrCode->metadata['title'] ?? 'qrcode') . '.png';

    // Audit log
    $this->auditService->log(
        'QRCODE_DOWNLOADED',
        $qrCode,
        'QR code downloaded',
        null,
        ['filename' => $downloadName]
    );

    return Storage::disk('public')->download($relativeDiskPath, $downloadName);
}

    /**
     * Revoke QR code
     */
    public function revoke($id)
    {
        $user = Auth::user();

        $qrCode = BarcodeToken::where('id', $id)
            ->where('user_id', $user->id)
            ->whereNull('document_id')
            ->firstOrFail();

        $qrCode->update(['is_valid' => false]);

        // Audit log
        $this->auditService->log(
            'QRCODE_REVOKED',
            $qrCode,
            'QR code revoked',
            ['is_valid' => true],
            ['is_valid' => false]
        );

        return redirect()
            ->back()
            ->with('success', 'QR Code has been revoked');
    }

    /**
     * Delete QR code
     */
    public function destroy($id)
    {
        $user = Auth::user();

        $qrCode = BarcodeToken::where('id', $id)
            ->where('user_id', $user->id)
            ->whereNull('document_id')
            ->firstOrFail();

        // Delete barcode file
        $barcodePath = $qrCode->metadata['barcode_path'] ?? null;
        if ($barcodePath && Storage::exists($barcodePath)) {
            Storage::delete($barcodePath);
        }

        // Audit log
        $this->auditService->log(
            'QRCODE_DELETED',
            $qrCode,
            'QR code deleted',
            $qrCode->toArray(),
            null
        );

        $qrCode->delete();

        return redirect()
            ->route('qrcodes.index')
            ->with('success', 'QR Code deleted successfully');
    }
}
