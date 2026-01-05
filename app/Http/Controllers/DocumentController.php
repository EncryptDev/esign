<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use App\Services\DocumentService;
use App\Services\SignatureService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    private DocumentService $documentService;
    private SignatureService $signatureService;

    public function __construct(
        DocumentService $documentService,
        SignatureService $signatureService
    ) {
        $this->documentService = $documentService;
        $this->signatureService = $signatureService;
    }

    /**
     * Display a listing of documents
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $status = $request->get('status');

        $documents = $this->documentService->getUserDocuments($user, $status);

        return view('documents.index', compact('documents', 'status'));
    }

    /**
     * Show the form for creating a new document
     */
    public function create()
    {
        return view('documents.create');
    }

    /**
     * Store a newly created document (handled by Livewire component)
     */
    public function store(Request $request)
    {
        // This is handled by Livewire UploadDocument component
        return redirect()->route('documents.create');
    }

    /**
     * Display the specified document
     */
    public function show(string $uuid)
    {
        $user = Auth::user();
        $document = $this->documentService->getDocumentByUuid($uuid, $user);

        return view('documents.show', compact('document'));
    }

    /**
     * Show the form for mapping signature areas
     */
    public function mapAreas(string $uuid)
    {
        $user = Auth::user();
        $document = $this->documentService->getDocumentByUuid($uuid, $user);

        if (!$document->canBeEdited()) {
            return redirect()
                ->route('documents.show', $document->uuid)
                ->with('error', 'Cannot edit signature areas for non-draft documents');
        }

        return view('documents.map-areas', compact('document'));
    }

    /**
     * Show the signing page
     */
    public function signPage(string $uuid)
    {
        $user = Auth::user();
        $document = $this->documentService->getDocumentByUuid($uuid, $user);

        if (!$document->canBeSigned()) {
            return redirect()
                ->route('documents.show', $document->uuid)
                ->with('error', 'Document cannot be signed at this time');
        }

        return view('documents.sign', compact('document'));
    }

    /**
     * Process document signing
     */
    public function processSign(Request $request, string $uuid)
    {
        $user = Auth::user();
        $document = $this->documentService->getDocumentByUuid($uuid, $user);

        try {
            $signatures = $this->signatureService->signDocument($document);

            return redirect()
                ->route('documents.show', $document->uuid)
                ->with('success', 'Document signed successfully with ' . count($signatures) . ' signature(s)!');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to sign document: ' . $e->getMessage());
        }
    }

    /**
     * Download document
     */
    public function download(string $uuid, Request $request)
    {
        $user = Auth::user();
        $document = $this->documentService->getDocumentByUuid($uuid, $user);

        $versionType = $request->get('version', 'original');

        try {
            return $this->documentService->downloadDocument($document, $versionType);
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to download document: ' . $e->getMessage());
        }
    }

    /**
     * Show PDF preview for signature mapping
     */
    public function pdfPreview(string $uuid)
    {
        $user = Auth::user();
        $document = $this->documentService->getDocumentByUuid($uuid, $user);

        // Get original version
        $originalVersion = $document->versions()
            ->where('version_type', 'original')
            ->first();

        if (!$originalVersion || !Storage::exists($originalVersion->storage_path)) {
            abort(404, 'PDF file not found');
        }

        $filePath = Storage::path($originalVersion->storage_path);

        return response()->file($filePath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="preview.pdf"'
        ]);
    }

    /**
     * Remove the specified document
     */
    public function destroy(string $uuid)
    {
        $user = Auth::user();
        $document = $this->documentService->getDocumentByUuid($uuid, $user);

        try {
            $this->documentService->deleteDocument($document);

            return redirect()
                ->route('documents.index')
                ->with('success', 'Document deleted successfully');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to delete document: ' . $e->getMessage());
        }
    }
}
