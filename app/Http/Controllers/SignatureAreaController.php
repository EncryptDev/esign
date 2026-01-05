<?php

namespace App\Http\Controllers;

use App\Models\SignatureArea;
use App\Models\Document;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SignatureAreaController extends Controller
{
    private AuditService $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    /**
     * Store a new signature area
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'document_id' => 'required|exists:documents,id',
            'page_number' => 'required|integer|min:1',
            'position_x' => 'required|numeric|min:0',
            'position_y' => 'required|numeric|min:0',
            'width' => 'required|numeric|min:10',
            'height' => 'required|numeric|min:10',
            'label' => 'nullable|string|max:255',
        ]);

        // Verify document ownership
        $document = Document::findOrFail($validated['document_id']);

        if ($document->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check if document can be edited
        if (!$document->canBeEdited()) {
            return response()->json(['error' => 'Document cannot be edited'], 422);
        }

        // Create signature area
        $signatureArea = SignatureArea::create([
            'uuid' => Str::uuid(),
            'document_id' => $validated['document_id'],
            'page_number' => $validated['page_number'],
            'position_x' => $validated['position_x'],
            'position_y' => $validated['position_y'],
            'width' => $validated['width'],
            'height' => $validated['height'],
            'label' => $validated['label'] ?? 'Signature Area',
            'is_signed' => false,
        ]);

        // Audit log
        $this->auditService->log(
            'SIGNATURE_AREA_CREATED',
            $signatureArea,
            'Signature area created via interactive mapper',
            null,
            $signatureArea->toArray()
        );

        return response()->json([
            'id' => $signatureArea->id,
            'uuid' => $signatureArea->uuid,
            'message' => 'Signature area created successfully'
        ], 201);
    }

    /**
     * Update signature area position
     */
    public function update(Request $request, SignatureArea $signatureArea)
    {
        // Verify ownership
        if ($signatureArea->document->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check if area can be modified
        if ($signatureArea->is_signed) {
            return response()->json(['error' => 'Cannot modify signed area'], 422);
        }

        $validated = $request->validate([
            'position_x' => 'required|numeric|min:0',
            'position_y' => 'required|numeric|min:0',
            'width' => 'required|numeric|min:10',
            'height' => 'required|numeric|min:10',
        ]);

        $oldValues = $signatureArea->only(['position_x', 'position_y', 'width', 'height']);

        $signatureArea->update($validated);

        // Audit log
        $this->auditService->log(
            'SIGNATURE_AREA_UPDATED',
            $signatureArea,
            'Signature area position updated',
            $oldValues,
            $validated
        );

        return response()->json([
            'message' => 'Signature area updated successfully'
        ]);
    }

    /**
     * Delete signature area
     */
    public function destroy(SignatureArea $signatureArea)
    {
        // Verify ownership
        if ($signatureArea->document->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check if area can be deleted
        if ($signatureArea->is_signed) {
            return response()->json(['error' => 'Cannot delete signed area'], 422);
        }

        // Audit log before deletion
        $this->auditService->log(
            'SIGNATURE_AREA_DELETED',
            $signatureArea,
            'Signature area deleted via interactive mapper',
            $signatureArea->toArray(),
            null
        );

        $signatureArea->delete();

        return response()->json([
            'message' => 'Signature area deleted successfully'
        ]);
    }
}
