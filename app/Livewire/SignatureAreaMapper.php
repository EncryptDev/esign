<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Document;
use App\Models\SignatureArea;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SignatureAreaMapper extends Component
{
    public Document $document;
    public $signatureAreas = [];
    public $newArea = [
        'page_number' => '',
        'position_x' => '',
        'position_y' => '',
        'width' => '',
        'height' => '',
        'label' => '',
    ];

    protected $rules = [
        'newArea.page_number' => 'required|integer|min:1',
        'newArea.position_x' => 'required|numeric|min:0',
        'newArea.position_y' => 'required|numeric|min:0',
        'newArea.width' => 'required|numeric|min:10',
        'newArea.height' => 'required|numeric|min:10',
        'newArea.label' => 'nullable|string|max:255',
    ];

    public function mount(Document $document)
    {
        // Verify ownership
        if ($document->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access');
        }

        $this->document = $document;
        $this->loadSignatureAreas();
    }

    /**
     * Load existing signature areas
     */
    public function loadSignatureAreas()
    {
        $this->signatureAreas = $this->document->signatureAreas()
            ->orderBy('page_number')
            ->orderBy('created_at')
            ->get()
            ->map(function ($area) {
                return [
                    'id' => $area->id,
                    'uuid' => $area->uuid,
                    'page_number' => $area->page_number,
                    'position_x' => $area->position_x,
                    'position_y' => $area->position_y,
                    'width' => $area->width,
                    'height' => $area->height,
                    'label' => $area->label,
                    'is_signed' => $area->is_signed,
                ];
            })
            ->toArray();
    }

    /**
     * Add new signature area
     */
    public function addSignatureArea()
    {
        $this->validate();

        try {
            $signatureArea = SignatureArea::create([
                'uuid' => Str::uuid(),
                'document_id' => $this->document->id,
                'page_number' => $this->newArea['page_number'],
                'position_x' => $this->newArea['position_x'],
                'position_y' => $this->newArea['position_y'],
                'width' => $this->newArea['width'],
                'height' => $this->newArea['height'],
                'label' => $this->newArea['label'] ?: 'Signature Area ' . (count($this->signatureAreas) + 1),
                'is_signed' => false,
            ]);

            // Audit log
            app(\App\Services\AuditService::class)->log(
                'SIGNATURE_AREA_CREATED',
                $signatureArea,
                'Signature area created on page ' . $this->newArea['page_number']
            );

            // Reload areas
            $this->loadSignatureAreas();

            // Reset form
            $this->resetNewArea();

            session()->flash('success', 'Signature area added successfully');

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to add signature area: ' . $e->getMessage());
        }
    }

    /**
     * Reset new area form
     */
    public function resetNewArea()
    {
        $this->newArea = [
            'page_number' => '',
            'position_x' => '',
            'position_y' => '',
            'width' => 60,
            'height' => 30,
            'label' => '',
        ];
        $this->resetValidation();
    }

    /**
     * Delete signature area
     *
     * @param string $uuid
     */
    public function deleteArea($uuid)
    {
        try {
            $area = SignatureArea::where('uuid', $uuid)
                ->where('document_id', $this->document->id)
                ->where('is_signed', false) // Only allow deleting unsigned areas
                ->firstOrFail();

            // Audit log
            app(\App\Services\AuditService::class)->log(
                'SIGNATURE_AREA_DELETED',
                $area,
                'Signature area deleted from page ' . $area->page_number
            );

            $area->delete();

            // Reload areas
            $this->loadSignatureAreas();

            // Notify frontend
            $this->dispatch('areaDeletedSuccess', uuid: $uuid);

            session()->flash('success', 'Signature area deleted');

        } catch (\Exception $e) {
            $this->dispatch('showError', message: 'Failed to delete area: ' . $e->getMessage());
        }
    }



    /**
     * Proceed to signing
     */
    public function proceedToSigning()
    {
        if (empty($this->signatureAreas)) {
            session()->flash('error', 'Please add at least one signature area before proceeding');
            return;
        }

        return redirect()->route('documents.sign', $this->document->uuid);
    }

    public function render()
    {
        return view('livewire.signature-area-mapper');
    }
}
