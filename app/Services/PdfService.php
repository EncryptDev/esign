<?php

namespace App\Services;

use setasign\Fpdi\Tcpdf\Fpdi;
use Illuminate\Support\Facades\Storage;
use App\Models\Document;
use App\Models\SignatureArea;

class PdfService
{
public function stampPdfWithBarcodes(Document $document, array $stampData): string
    {
       $pdf = new Fpdi('P', 'mm', 'A4');

    // ✅ KRITIKAL: Set margin 0 untuk semua halaman
    $pdf->SetMargins(0, 0, 0);
    $pdf->SetAutoPageBreak(false);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    $originalPath = Storage::path($document->storage_path);
    $pageCount = $pdf->setSourceFile($originalPath);

    for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
        $templateId = $pdf->importPage($pageNo);
        $size = $pdf->getTemplateSize($templateId);
        $orientation = $size['width'] > $size['height'] ? 'L' : 'P';

        // ✅ Tambahkan halaman dengan ukuran PERSIS
        $pdf->AddPage($orientation, [$size['width'], $size['height']]);

        // ✅ Template ditempel PENUH tanpa offset
        $pdf->useTemplate($templateId, 0, 0, $size['width'], $size['height'], true);

        // ✅ Render stamps
        foreach ($stampData as $stamp) {
            if ($stamp['signature_area']->page_number === $pageNo) {
                $this->addStampToPage(
                    $pdf,
                    $stamp['barcode_path'],
                    $stamp['signature_area'],
                    $stamp['signature'],
                    $size
                );
            }
        }
    }

        // ... kode simpan file (sama seperti sebelumnya) ...
        $filename = $document->uuid . '_signed_' . time() . '.pdf';
        $outputPath = 'documents/' . $document->user->uuid . '/' . $document->uuid . '/versions/' . $filename;
        $fullPath = Storage::path($outputPath);

        if (!file_exists(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0755, true);
        }

        $pdf->Output($fullPath, 'F');
        return $outputPath;
    }

private function addStampToPage(
    Fpdi $pdf,
    string $barcodeAbsolutePath,
    SignatureArea $signatureArea,
    $signature,
    array $pageSize
): void {
    $x = $signatureArea->position_x;
    $yFromTop = $signatureArea->position_y;
    $width = $signatureArea->width;
    $height = $signatureArea->height;

    // 🔧 Inversi Y
    $yPdf = $pageSize['height'] - $yFromTop - $height;

    // ✅ Extended Logging
    \Log::info('🖨️ Stamping Details', [
        'signature_area_id' => $signatureArea->id,
        'page' => $signatureArea->page_number,
        'input_coords_from_db' => [
            'x' => $x,
            'y_from_top' => $yFromTop,
            'width' => $width,
            'height' => $height
        ],
        'page_size_from_template' => [
            'width' => $pageSize['width'],
            'height' => $pageSize['height']
        ],
        'calculated_y_inversion' => [
            'formula' => "{$pageSize['height']} - {$yFromTop} - {$height}",
            'y_pdf' => $yPdf
        ],
        'final_image_params' => [
            'x' => $x,
            'y' => $yPdf,
            'width' => $width,
            'height' => $height
        ]
    ]);

    if (file_exists($barcodeAbsolutePath)) {
        try {
            $pdf->Image(
                $barcodeAbsolutePath,
                $x,
                $yPdf,
                $width,
                $height,
                'PNG'
            );
        } catch (\Exception $e) {
            \Log::error('❌ Stamp Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
        }
    } else {
        \Log::error('❌ Barcode file not found', [
            'path' => $barcodeAbsolutePath
        ]);
    }
}
    /**
     * Extract PDF metadata
     *
     * @param string $filePath
     * @return array
     */
    public function extractMetadata(string $filePath): array
    {
        try {
            $pdf = new Fpdi();
            $pageCount = $pdf->setSourceFile($filePath);

            $metadata = [
                'page_count' => $pageCount,
                'file_size' => filesize($filePath),
                'checksum' => hash_file('sha256', $filePath),
            ];

            // Get dimensions of first page
            if ($pageCount > 0) {
                $templateId = $pdf->importPage(1);
                $size = $pdf->getTemplateSize($templateId);
                $metadata['first_page_width'] = $size['width'];
                $metadata['first_page_height'] = $size['height'];
            }

            return $metadata;
        } catch (\Exception $e) {
            // Return basic metadata if PDF parsing fails
            return [
                'page_count' => 1,
                'file_size' => filesize($filePath),
                'checksum' => hash_file('sha256', $filePath),
            ];
        }
    }

    /**
     * Validate PDF integrity
     *
     * @param string $filePath
     * @return bool
     */
    public function validatePdf(string $filePath): bool
    {
        try {
            // Check if file exists
            if (!file_exists($filePath)) {
                return false;
            }

            // Check if file is readable
            if (!is_readable($filePath)) {
                return false;
            }

            // Check file size (must be > 0 and < 50MB)
            $fileSize = filesize($filePath);
            if ($fileSize === 0 || $fileSize > 50 * 1024 * 1024) {
                return false;
            }

            // Try to parse PDF
            $pdf = new Fpdi();
            $pageCount = $pdf->setSourceFile($filePath);

            return $pageCount > 0;
        } catch (\Exception $e) {
            \Log::error('PDF validation failed: ' . $e->getMessage(), [
                'file' => $filePath,
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
}
