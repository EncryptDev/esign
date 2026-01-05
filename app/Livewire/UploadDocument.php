<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Services\DocumentService;
use App\Services\PdfService;
use Illuminate\Support\Facades\Auth;

class UploadDocument extends Component
{
    use WithFileUploads;

    // Form properties
    public $file = null;
    public $title = '';
    public $description = '';
    public $purpose = '';

    // State properties
    public bool $uploading = false;
    public int $uploadProgress = 0;
    public $uploadedDocument = null;
    public bool $isProcessing = false;

    // Validation rules
    protected $rules = [
        'file' => 'required|file|mimes:pdf|max:10240', // 10MB max
        'title' => 'required|string|min:3|max:255',
        'description' => 'nullable|string|max:1000',
        'purpose' => 'nullable|string|max:500',
    ];

    protected $messages = [
        'file.required' => 'Please select a PDF document to upload.',
        'file.mimes' => 'Only PDF files are allowed.',
        'file.max' => 'File size must not exceed 10MB.',
        'title.required' => 'Document title is required.',
        'title.min' => 'Title must be at least 3 characters.',
    ];

    public function mount()
    {
        // Initialize empty form
        $this->resetForm();
    }

    /**
     * Upload document
     */
    public function upload()
    {
        // Validate form
        $this->validate();

        try {
            $this->isProcessing = true;
            $this->uploading = true;
            $this->uploadProgress = 0;

            // Get services
            $documentService = app(DocumentService::class);
            $pdfService = app(PdfService::class);

            // Validate PDF integrity
            $this->uploadProgress = 20;
            $tempPath = $this->file->getRealPath();

            if (!$pdfService->validatePdf($tempPath)) {
                throw new \Exception('Invalid or corrupted PDF file');
            }

            // Extract metadata
            $this->uploadProgress = 40;
            $metadata = $pdfService->extractMetadata($tempPath);

            // Upload document
            $this->uploadProgress = 60;
            $document = $documentService->uploadDocument(
                Auth::user(),
                $this->file,
                $this->title,
                $this->description,
                $this->purpose,
                $metadata
            );

            $this->uploadProgress = 100;
            $this->uploadedDocument = $document;

            // Success notification
            session()->flash('success', 'Document uploaded successfully!');

            // Reset states
            $this->uploading = false;
            $this->isProcessing = false;

            // Redirect to document detail page
            $this->redirect(route('documents.show', $document->uuid), navigate: true);

        } catch (\Exception $e) {
            $this->addError('upload', 'Upload failed: ' . $e->getMessage());
            $this->uploading = false;
            $this->isProcessing = false;
            $this->uploadProgress = 0;
        }
    }

    /**
     * Reset form
     */
    public function resetForm()
    {
        $this->reset(['file', 'title', 'description', 'purpose', 'uploading', 'uploadProgress', 'uploadedDocument', 'isProcessing']);
        $this->resetValidation();
        $this->resetErrorBag();
    }

    /**
     * Update filename when file selected
     */
    public function updatedFile()
    {
        // Validate file immediately
        $this->validateOnly('file');

        // Auto-fill title from filename if empty
        if (empty($this->title) && $this->file) {
            try {
                $filename = $this->file->getClientOriginalName();
                $this->title = pathinfo($filename, PATHINFO_FILENAME);
            } catch (\Exception $e) {
                // If error getting filename, just skip auto-fill
            }
        }
    }

    public function render()
    {
        return view('livewire.upload-document');
    }
}
