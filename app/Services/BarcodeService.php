<?php

namespace App\Services;

use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Picqer\Barcode\BarcodeGeneratorPNG;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class BarcodeService
{
    private BarcodeGeneratorPNG $generator;

    public function __construct()
    {
        $this->generator = new BarcodeGeneratorPNG();
    }

public function generateQRCodeWithLogo($url, $logoPath)
{
    // Gunakan nama file unik
    $fileName = 'qr_' . uniqid() . '.png';

    // Simpan di folder public
    // storage_path() menghasilkan path absolut: C:\xampp\htdocs\project\storage\app\public\...
    $absolutePath = storage_path('app/public/' . $fileName);

    // Pastikan folder ada
    if (!file_exists(dirname($absolutePath))) {
        mkdir(dirname($absolutePath), 0755, true);
    }

    // Generate QR Code (Tanpa Logo dulu untuk tes kestabilan)
    // Jika library imagick error, kita pakai format standar
    QrCode::format('png')
           ->size(500) // Resolusi tinggi
           ->margin(1)
           ->generate($url, $absolutePath);

    // Return ABSOLUTE PATH agar PdfService bisa langsung baca
    return $absolutePath;
}

    /**
     * Generate encrypted token for barcode
     *
     * @param int $documentId
     * @param int $userId
     * @param int $signatureId
     * @return string Encrypted token
     */
    public function generateSecureToken(
        int $documentId,
        int $userId,
        int $signatureId
    ): string {
        $payload = [
            'uuid' => Str::uuid()->toString(),
            'document_id' => $documentId,
            'user_id' => $userId,
            'signature_id' => $signatureId,
            'timestamp' => now()->timestamp,
            'random' => Str::random(32),
        ];

        return encrypt(json_encode($payload));
    }

    /**
     * Decrypt and validate token
     *
     * @param string $encryptedToken
     * @return array|null Decrypted payload or null if invalid
     */
    public function validateToken(string $encryptedToken): ?array
    {
        try {
            $json = decrypt($encryptedToken);
            $payload = json_decode($json, true);

            if (!isset($payload['uuid'], $payload['document_id'], $payload['user_id'])) {
                return null;
            }

            return $payload;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Generate verification URL
     *
     * @param string $encryptedToken
     * @return string Full verification URL
     */
    public function generateVerificationUrl(string $encryptedToken): string
    {
        return route('verification.show', [
            'token' => base64_encode($encryptedToken)
        ]);
    }
}
