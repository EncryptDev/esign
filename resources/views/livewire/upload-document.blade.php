<div>
    @if (session()->has('success'))
        <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->has('upload'))
        <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            {{ $errors->first('upload') }}
        </div>
    @endif

    <form wire:submit.prevent="upload">
        <!-- File Upload -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                PDF Document <span class="text-red-500">*</span>
            </label>

            @if($file)
                <!-- File Selected View -->
                <div class="border-2 border-indigo-400 bg-indigo-50 rounded-lg p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <svg class="w-12 h-12 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <div>
                                <p class="font-medium text-gray-900">{{ $file->getClientOriginalName() }}</p>
                                <p class="text-sm text-gray-600">{{ number_format($file->getSize() / 1024, 2) }} KB</p>
                            </div>
                        </div>
                        <button type="button"
                                wire:click="$set('file', null)"
                                class="text-red-600 hover:text-red-800 font-medium">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            @else
                <!-- File Upload View -->
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-indigo-400 transition">
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <div class="mb-4">
                        <label for="file-upload" class="cursor-pointer">
                            <span class="text-indigo-600 hover:text-indigo-500 font-medium">Click to upload</span>
                            <span class="text-gray-600"> or drag and drop</span>
                            <input id="file-upload"
                                   type="file"
                                   wire:model="file"
                                   accept=".pdf,application/pdf"
                                   class="sr-only">
                        </label>
                    </div>
                    <p class="text-xs text-gray-500">PDF files only, up to 10MB</p>
                </div>
            @endif

            @error('file')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror

            <!-- Loading States -->
            <div wire:loading wire:target="file" class="mt-4">
                <div class="flex items-center text-indigo-600">
                    <svg class="animate-spin h-5 w-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-sm font-medium">Loading file...</span>
                </div>
            </div>

            @if($uploading && $uploadProgress > 0)
                <div class="mt-4">
                    <div class="flex items-center justify-between text-sm text-gray-700 mb-2">
                        <span>Uploading document...</span>
                        <span>{{ $uploadProgress }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div class="bg-indigo-600 h-2.5 rounded-full transition-all duration-300" style="width: {{ $uploadProgress }}%"></div>
                    </div>
                </div>
            @endif

            <div wire:loading wire:target="upload" class="mt-4">
                <div class="flex items-center justify-center text-indigo-600">
                    <svg class="animate-spin h-5 w-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-sm font-medium">Processing your document...</span>
                </div>
            </div>
        </div>

        <!-- Title -->
        <div class="mb-6">
            <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                Document Title <span class="text-red-500">*</span>
            </label>
            <input type="text"
                   id="title"
                   wire:model="title"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                   placeholder="Enter document title">
            @error('title')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Description -->
        <div class="mb-6">
            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                Description
            </label>
            <textarea id="description"
                      wire:model="description"
                      rows="3"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                      placeholder="Enter document description (optional)"></textarea>
            @error('description')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Purpose -->
        <div class="mb-6">
            <label for="purpose" class="block text-sm font-medium text-gray-700 mb-2">
                Purpose
            </label>
            <input type="text"
                   id="purpose"
                   wire:model="purpose"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                   placeholder="e.g., Employment Contract, NDA, Agreement">
            @error('purpose')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Buttons -->
        <div class="flex items-center justify-between pt-6 border-t border-gray-200">
            <button type="button"
                    wire:click="resetForm"
                    class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium"
                    wire:loading.attr="disabled">
                Reset Form
            </button>
            <button type="submit"
                    class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                    wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="upload">Upload Document</span>
                <span wire:loading wire:target="upload">Uploading...</span>
            </button>
        </div>
    </form>
</div>
