<div>

    <div x-data="signatureMapper()">
        <!-- Instructions -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
            <div class="flex">
                <svg class="w-6 h-6 text-blue-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <h4 class="font-semibold text-blue-900 mb-2">How to Map Signature Areas</h4>
                    <ol class="text-sm text-blue-800 space-y-1 list-decimal list-inside">
                        <li><strong>Draw:</strong> Click and drag on the PDF to create a signature area</li>
                        <li><strong>Move:</strong> Click and drag an existing area to reposition it</li>
                        <li><strong>Delete:</strong> Double-click on an area to delete it</li>
                        <li><strong>Navigate:</strong> Use page controls to add areas on different pages</li>
                        <li>All changes are saved automatically</li>
                    </ol>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- PDF Viewer (Left Side - 2/3 width) -->
            <div class="lg:col-span-2">
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Document Preview</h3>

                        <!-- Page Controls -->
                        <div class="flex items-center space-x-3">
                            <button @click="previousPage()" :disabled="currentPage <= 1"
                                class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </button>

                            <span class="text-sm text-gray-700">
                                Page <span x-text="currentPage"></span> of <span x-text="totalPages"></span>
                            </span>

                            <button @click="nextPage()" :disabled="currentPage >= totalPages"
                                class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- PDF Canvas Container -->
                    <div class="border-2 border-gray-200 rounded-lg overflow-auto bg-gray-100"
                        style="max-height: 800px;">
                        <div wire:ignore id="pdf-container" class="inline-block"></div>
                    </div>

                    <!-- Loading State -->
                    <div x-show="loading" class="text-center py-8">
                        <svg class="animate-spin h-8 w-8 text-indigo-600 mx-auto" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        <p class="text-gray-600 mt-2">Loading PDF...</p>
                    </div>

                    <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-600">
                            <strong>💡 Tips:</strong>
                            • Draw rectangles where you want signatures to appear
                            • Recommended size: approximately 60mm x 30mm
                            • You can add multiple areas on the same page
                        </p>
                    </div>
                </div>
            </div>

            <!-- Signature Areas List (Right Side - 1/3 width) -->
            <div class="lg:col-span-1">
                <div class="bg-white shadow-sm rounded-lg p-6 sticky top-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        Signature Areas (<span x-text="areas.length"></span>)
                    </h3>

                    <div x-show="areas.length === 0"
                        class="text-center py-8 border-2 border-dashed border-gray-300 rounded-lg">
                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                            </path>
                        </svg>
                        <p class="text-sm text-gray-500">No areas yet</p>
                        <p class="text-xs text-gray-400 mt-1">Draw on the PDF to create</p>
                    </div>

                    <div x-show="areas.length > 0" class="space-y-2 max-h-96 overflow-y-auto">
                        <template x-for="(area, index) in areas" :key="area.id">
                            <div class="border border-gray-200 rounded-lg p-3 hover:border-indigo-300 transition">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-900 text-sm" x-text="'Area ' + (index + 1)"></p>
                                        <p class="text-xs text-gray-600">Page <span x-text="area.page"></span></p>
                                        <div class="mt-1 text-xs text-gray-500">
                                            <div>Position: <span x-text="area.pdfX"></span>mm, <span
                                                    x-text="area.pdfY"></span>mm</div>
                                            <div>Size: <span x-text="area.pdfWidth"></span>mm × <span
                                                    x-text="area.pdfHeight"></span>mm</div>
                                        </div>
                                    </div>
                                    <button @click="deleteArea(area.id)" class="ml-2 text-red-600 hover:text-red-800">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                            </path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-6 space-y-2">
                        <button @click="proceedToSigning()" :disabled="areas.length === 0"
                            class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                                </path>
                            </svg>
                            Proceed to Signing
                        </button>

                        <a href="{{ route('documents.show', $document->uuid) }}"
                            class="w-full block text-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium">
                            Back to Document
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        <div x-show="message" x-transition
            class="fixed bottom-4 right-4 max-w-sm bg-white border rounded-lg shadow-lg p-4"
            :class="messageType === 'success' ? 'border-green-400' : 'border-red-400'">
            <div class="flex items-center">
                <svg x-show="messageType === 'success'" class="w-5 h-5 text-green-600 mr-2" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <svg x-show="messageType === 'error'" class="w-5 h-5 text-red-600 mr-2" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span x-text="message" class="text-sm"
                    :class="messageType === 'success' ? 'text-green-800' : 'text-red-800'"></span>
            </div>
        </div>
    </div>

    @script
        <script>
            window.signatureMapper = function() {
                // --- PERBAIKAN DI SINI ---
                // Kita definisikan variabel di dalam function scope, tapi DI LUAR objek return.
                // Ini menciptakan "Closure". Alpine tidak akan bisa melihat atau meng-proxy variabel ini.
                // PDF.js akan aman dari error "private member".
                let pdfInstance = null;

                return {
                    loading: true,
                    currentPage: 1,
                    totalPages: 0,
                    areas: @json($signatureAreas) || [],
                    message: '',
                    messageType: 'success',

                    // JANGAN definisikan pdfViewerInstance di sini
                    // Hapus baris: pdfViewerInstance: null,

                    async init() {
                        try {
                            if (!window.PDFViewer) {
                                console.error('PDFViewer class not found.');
                                return;
                            }

                            // Gunakan variabel closure 'pdfInstance' (tanpa 'this.')
                            pdfInstance = new window.PDFViewer('pdf-container', {
                                // Arrow function agar 'this' tetap merujuk ke Alpine component
                                onAreaAdded: (area) => this.handleAreaAdded(area),
                                onAreaUpdated: (area) => this.handleAreaUpdated(area),
                                onAreaDeleted: (area) => this.handleAreaDeleted(area)
                            });

                            const pdfUrl = '{{ route('documents.show', $document->uuid) }}/pdf-preview';

                            // Panggil method pada variabel closure
                            await pdfInstance.loadPDF(pdfUrl);

                            // Ambil data dari instance ke state Alpine
                            this.totalPages = pdfInstance.totalPages;
                            this.currentPage = pdfInstance.currentPage;

                            if (this.areas.length > 0) {
                                pdfInstance.loadExistingAreas(this.areas);
                            }

                            this.loading = false;
                        } catch (error) {
                            console.error('Failed to load PDF:', error);
                            this.showMessage('Failed to load PDF preview', 'error');
                            this.loading = false;
                        }
                    },

                    showMessage(text, type = 'success') {
                        this.message = text;
                        this.messageType = type;
                        setTimeout(() => {
                            this.message = '';
                        }, 3000);
                    },

                    async nextPage() {
                        // Akses variabel closure langsung
                        if (this.currentPage < this.totalPages) {
                            await pdfInstance.changePage(this.currentPage + 1);
                            this.currentPage = pdfInstance.currentPage;
                        }
                    },

                    async previousPage() {
                        // Akses variabel closure langsung
                        if (this.currentPage > 1) {
                            await pdfInstance.changePage(this.currentPage - 1);
                            this.currentPage = pdfInstance.currentPage;
                        }
                    },

                    deleteArea(id) {
                        // Panggil method delete di class PDFViewer
                        // Ini akan menghapus visual kotak, lalu memanggil callback onAreaDeleted
                        if (pdfInstance) {
                            pdfInstance.deleteArea(id);
                        }
                    },

                    proceedToSigning() {
                        // 1. Validasi sederhana di sisi client
                        if (this.areas.length === 0) {
                            this.showMessage('Please add at least one signature area.', 'error');
                            return;
                        }

                        // 2. Panggil Method Livewire (PHP)
                        // Pastikan di Class Livewire anda ada method public function proceedToSigning()
                        this.$wire.proceedToSigning();
                    },

                    handleAreaAdded(area) {

                        const pageWidth = pdfInstance ? pdfInstance.pageWidthMM : 0;
                        const pageHeight = pdfInstance ? pdfInstance.pageHeightMM : 0;

                        console.log('📍 Area Mapping:', {
                            '🖼️ Canvas (pixels)': {
                                x: area.x,
                                y: area.y,
                                w: area.width,
                                h: area.height
                            },
                            '📄 PDF (mm from top)': {
                                x: area.pdfX,
                                y: area.pdfY,
                                w: area.pdfWidth,
                                h: area.pdfHeight
                            },
                            '📏 Page Size (mm)': {
                                width: pageWidth.toFixed(2),
                                height: pageHeight.toFixed(2)
                            },
                            '🔍 Y Calculation': {
                                formula: `${pageHeight.toFixed(2)} - ${area.pdfY} - ${area.pdfHeight}`,
                                result: (pageHeight - area.pdfY - area.pdfHeight).toFixed(2)
                            }
                        });

                        this.areas.push(area);
                        this.$wire.set('newArea.page_number', area.page);
                        this.$wire.set('newArea.position_x', area.pdfX);
                        this.$wire.set('newArea.position_y', area.pdfY);
                        this.$wire.set('newArea.width', area.pdfWidth);
                        this.$wire.set('newArea.height', area.pdfHeight);
                    },

                    handleAreaUpdated(area) {
                        const index = this.areas.findIndex(a => a.id === area.id);
                        if (index !== -1) this.areas[index] = area;
                    },

                    handleAreaDeleted(area) {
                        this.areas = this.areas.filter(a => a.id !== area.id);
                    }
                }
            }
        </script>
    @endscript


    <!-- Instructions -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
        <div class="flex">
            <svg class="w-6 h-6 text-blue-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div>
                <h4 class="font-semibold text-blue-900 mb-2">How to Map Signature Areas</h4>
                <ol class="text-sm text-blue-800 space-y-1 list-decimal list-inside">
                    <li>Use the form below to define signature areas on your document</li>
                    <li>Specify the page number where you want to place the signature</li>
                    <li>Enter the position coordinates (in pixels from top-left)</li>
                    <li>Set the dimensions (width and height) for the signature stamp</li>
                    <li>Add multiple signature areas as needed</li>
                    <li>Once complete, proceed to sign the document</li>
                </ol>
            </div>
        </div>
    </div>

    <!-- Add Signature Area Form -->
    <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Add New Signature Area</h3>

        <form wire:submit.prevent="addSignatureArea" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Page Number -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Page Number <span class="text-red-500">*</span>
                    </label>
                    <select wire:model="newArea.page_number"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Select page</option>
                        @for ($i = 1; $i <= $document->page_count; $i++)
                            <option value="{{ $i }}">Page {{ $i }}</option>
                        @endfor
                    </select>
                    @error('newArea.page_number')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Label -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Label
                    </label>
                    <input type="text" wire:model="newArea.label" placeholder="e.g., CEO Signature"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    @error('newArea.label')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Position X -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Position X (mm) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" wire:model="newArea.position_x" step="0.01" placeholder="e.g., 50"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    @error('newArea.position_x')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Position Y -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Position Y (mm) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" wire:model="newArea.position_y" step="0.01" placeholder="e.g., 200"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    @error('newArea.position_y')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Width -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Width (mm) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" wire:model="newArea.width" step="0.01" placeholder="e.g., 60"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    @error('newArea.width')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Height -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Height (mm) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" wire:model="newArea.height" step="0.01" placeholder="e.g., 30"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    @error('newArea.height')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit"
                    class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium">
                    Add Signature Area
                </button>
            </div>
        </form>

        <!-- Helper Text -->
        <div class="mt-4 p-4 bg-gray-50 rounded-lg">
            <p class="text-sm text-gray-600">
                <strong>💡 Tip:</strong> Standard A4 page size is 210mm x 297mm.
                Recommended signature size is 60mm x 30mm. Position coordinates start from top-left corner (0,0).
            </p>
        </div>
    </div>

    <!-- Existing Signature Areas -->
    <div class="bg-white shadow-sm rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">
            Signature Areas ({{ count($signatureAreas) }})
        </h3>

        @if (empty($signatureAreas))
            <div class="text-center py-12 border-2 border-dashed border-gray-300 rounded-lg">
                <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                    </path>
                </svg>
                <p class="text-gray-500 font-medium mb-2">No signature areas defined yet</p>
                <p class="text-sm text-gray-400">Add your first signature area using the form above</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach ($signatureAreas as $index => $area)
                    <div
                        class="border border-gray-200 rounded-lg p-4 {{ $area['is_signed'] ? 'bg-gray-50' : 'bg-white' }}">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3 mb-2">
                                    <h4 class="font-semibold text-gray-900">
                                        {{ $area['label'] ?: 'Signature Area ' . ($index + 1) }}</h4>
                                    @if ($area['is_signed'])
                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800">
                                            Signed
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                            Unsigned
                                        </span>
                                    @endif
                                </div>
                                <div class="grid grid-cols-2 md:grid-cols-5 gap-2 text-sm text-gray-600">
                                    <div>
                                        <span class="font-medium">Page:</span> {{ $area['page_number'] }}
                                    </div>
                                    <div>
                                        <span class="font-medium">X:</span> {{ $area['position_x'] }}mm
                                    </div>
                                    <div>
                                        <span class="font-medium">Y:</span> {{ $area['position_y'] }}mm
                                    </div>
                                    <div>
                                        <span class="font-medium">W:</span> {{ $area['width'] }}mm
                                    </div>
                                    <div>
                                        <span class="font-medium">H:</span> {{ $area['height'] }}mm
                                    </div>
                                </div>
                            </div>
                            @if (!$area['is_signed'])
                                <button wire:click="deleteArea('{{ $area['uuid'] }}')"
                                    wire:confirm="Are you sure you want to delete this signature area?"
                                    class="ml-4 text-red-600 hover:text-red-800">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                        </path>
                                    </svg>
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Action Buttons -->
    <div class="flex items-center justify-between mt-6">
        <a href="{{ route('documents.show', $document->uuid) }}"
            class="px-6 py-3 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50">
            Back to Document
        </a>

        @if (!empty($signatureAreas))
            <button wire:click="proceedToSigning"
                class="px-8 py-3 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z">
                    </path>
                </svg>
                Proceed to Signing
            </button>
        @endif
    </div>

    @if (session()->has('success'))
        <div
            class="fixed bottom-4 right-4 bg-green-100 border border-green-400 text-green-700 px-6 py-4 rounded-lg shadow-lg">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div
            class="fixed bottom-4 right-4 bg-red-100 border border-red-400 text-red-700 px-6 py-4 rounded-lg shadow-lg">
            {{ session('error') }}
        </div>
    @endif
</div>


</div>
