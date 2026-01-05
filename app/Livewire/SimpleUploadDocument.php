<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Validate;
use App\Services\DocumentService;
use App\Services\PdfService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SimpleUploadDocument extends Component
{
    use WithFileUploads;

    #[Validate('required|file|mimes:pdf|max:10240')]
    public $document;

    #[Validate('required|string|min:3|max:255')]
    public $title = '';

    #[Validate('nullable|string|max:1000')]
    public $description = '';

    #[Validate('nullable|string|max:500')]
    public $purpose = '';

    public function save()
    {
        $this->validate();

        try {
            Log::info('Starting document upload', [
                'user_id' => Auth::id(),
                'filename' => $this->document->getClientOriginalName()
            ]);

            $pdfService = app(PdfService::class);
            $documentService = app(DocumentService::class);

            // Get temp file path
            $tempPath = $this->document->getRealPath();

            Log::info('File uploaded to temp', ['path' => $tempPath]);

            // Validate PDF
            if (!$pdfService->validatePdf($tempPath)) {
                throw new \Exception('Invalid or corrupted PDF file');
            }

            // Extract metadata
            $metadata = $pdfService->extractMetadata($tempPath);

            Log::info('PDF metadata extracted', $metadata);

            // Upload document
            $doc = $documentService->uploadDocument(
                Auth::user(),
                $this->document,
                $this->title,
                $this->description,
                $this->purpose,
                $metadata
            );

            Log::info('Document uploaded successfully', [
                'document_id' => $doc->id,
                'uuid' => $doc->uuid
            ]);

            session()->flash('success', 'Document uploaded successfully!');

            return $this->redirect(route('documents.show', $doc->uuid), navigate: true);

        } catch (\Exception $e) {
            Log::error('Document upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->addError('document', 'Upload failed: ' . $e->getMessage());
        }
    }

    public function updatedDocument()
    {
        $this->validateOnly('document');

        // Auto-fill title if empty
        if (empty($this->title) && $this->document) {
            try {
                $filename = $this->document->getClientOriginalName();
                $this->title = pathinfo($filename, PATHINFO_FILENAME);
            } catch (\Exception $e) {
                Log::warning('Could not get filename', ['error' => $e->getMessage()]);
            }
        }
    }

    public function render()
    {
        return view('livewire.simple-upload-document');
    }
}
