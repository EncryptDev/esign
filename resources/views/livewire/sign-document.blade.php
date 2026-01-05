<div>
    <!-- Document Preview -->
    <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Document Preview</h3>
        <div class="border-2 border-gray-200 rounded-lg p-8 text-center bg-gray-50">
            <svg class="w-20 h-20 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <p class="text-gray-600 font-medium mb-2">{{ $document->title }}</p>
            <p class="text-sm text-gray-500">{{ $document->page_count }} page(s)</p>
        </div>
    </div>

    <!-- Signature Areas Summary -->
    <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Signature Areas to Sign</h3>
        @if($unsignedAreas->isEmpty())
            <div class="text-center py-8">
                <p class="text-gray-500">All signature areas have been signed</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach($unsignedAreas as $area)
                    <div class="border border-gray-200 rounded-lg p-4 flex items-center justify-between">
                        <div>
                            <p class="font-medium text-gray-900">{{ $area->label }}</p>
                            <p class="text-sm text-gray-600">Page {{ $area->page_number }}</p>
                        </div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            Pending
                        </span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Signer Information -->
    <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Your Signature Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                <input type="text"
                       value="{{ auth()->user()->name }}"
                       disabled
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Job Title</label>
                <input type="text"
                       value="{{ auth()->user()->job_title ?? 'N/A' }}"
                       disabled
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                <input type="text"
                       value="{{ auth()->user()->department ?? 'N/A' }}"
                       disabled
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Company</label>
                <input type="text"
                       value="{{ auth()->user()->company_name ?? 'N/A' }}"
                       disabled
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50">
            </div>
        </div>
        <p class="mt-4 text-sm text-gray-600">
            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            This information will be embedded in the digital signature
        </p>
    </div>

    <!-- Legal Disclaimer -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
        <div class="flex">
            <svg class="w-6 h-6 text-blue-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div>
                <h4 class="font-semibold text-blue-900 mb-2">Legal Disclaimer</h4>
                <p class="text-sm text-blue-800 mb-3">
                    By clicking "Sign Document" below, you acknowledge that:
                </p>
                <ul class="text-sm text-blue-800 space-y-1 list-disc list-inside">
                    <li>You have reviewed the document and agree to its contents</li>
                    <li>Your digital signature is legally binding</li>
                    <li>The signature will be cryptographically secured with a QR code</li>
                    <li>Anyone can verify your signature by scanning the QR code</li>
                    <li>Once signed, the document becomes immutable</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Terms Agreement -->
    <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
        <label class="flex items-start">
            <input type="checkbox"
                   wire:model.live="agreedToTerms"
                   class="mt-1 h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
            <span class="ml-3 text-sm text-gray-700">
                I have read and understood the document contents and agree to sign this document digitally.
                I understand that my digital signature is legally binding and equivalent to a handwritten signature.
            </span>
        </label>
        @error('agreedToTerms')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Action Buttons -->
    <div class="flex items-center justify-between">
        <a href="{{ route('documents.show', $document->uuid) }}"
           class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50">
            Cancel
        </a>
        <button type="button"
                wire:click="processSignDocument"
                wire:loading.attr="disabled"
                class="px-8 py-3 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
                @disabled(!$agreedToTerms)>
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
            </svg>
            <span wire:loading.remove wire:target="signDocument">Sign Document Now</span>
            <span wire:loading wire:target="signDocument">Signing...</span>
        </button>
    </div>

    <!-- Processing Overlay -->
    <div wire:loading.flex class="fixed inset-0 bg-gray-900 bg-opacity-50 z-50 items-center justify-center">
        <div class="bg-white rounded-lg p-8 max-w-sm mx-4">
            <div class="flex flex-col items-center">
                <svg class="animate-spin h-12 w-12 text-indigo-600 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Processing Signature</h3>
                <p class="text-sm text-gray-600 text-center">
                    Generating barcode and stamping document...
                </p>
            </div>
        </div>
    </div>
</div>
