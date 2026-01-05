<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Document;
use App\Services\SignatureService;
use Illuminate\Support\Facades\Auth;

class SignDocument extends Component
{
    public Document $document;
    public $unsignedAreas;
    public $agreedToTerms = false;

    protected $rules = [
        'agreedToTerms' => 'accepted',
    ];

    protected $messages = [
        'agreedToTerms.accepted' => 'You must agree to the terms before signing.',
    ];

    public function mount(Document $document)
    {
        // Verify ownership
        if ($document->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }

        // Check if document can be signed
        if (!$document->canBeSigned()) {
            session()->flash('error', 'This document cannot be signed at this time');
            return redirect()->route('documents.show', $document->uuid);
        }

        $this->document = $document;
        $this->loadUnsignedAreas();
    }

    public function loadUnsignedAreas()
    {
        $this->unsignedAreas = $this->document->signatureAreas()
            ->where('is_signed', false)
            ->get();
    }

    public function processSignDocument()
    {

        // Validate terms agreement
        $this->validate();

        try {
            $signatureService = app(SignatureService::class);
            $signatures = $signatureService->signDocument($this->document);

            session()->flash('success', 'Document signed successfully with ' . count($signatures) . ' signature(s)!');

            return redirect()->route('documents.show', $this->document->uuid);

        } catch (\Exception $e) {
            // session()->flash('error', 'Failed to sign document: ' . $e->getMessage());
            dd([
            'Message' => $e->getMessage(),
            'File' => $e->getFile(),
            'Line' => $e->getLine(),
            'Trace' => $e->getTraceAsString()
        ]);
        }
    }

    public function render()
    {
        return view('livewire.sign-document');
    }
}
