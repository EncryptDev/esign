<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $document->title }}
            </h2>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-{{ $document->status_color }}-100 text-{{ $document->status_color }}-800">
                {{ $document->status_label }}
            </span>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    {{ session('error') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Document Info -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Document Information</h3>
                            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Title</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $document->title }}</dd>
                                </div>
                                @if($document->purpose)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Purpose</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $document->purpose }}</dd>
                                </div>
                                @endif
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $document->status_label }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Pages</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $document->page_count }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">File Size</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $document->file_size_human }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Uploaded</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $document->created_at->format('M d, Y h:i A') }}</dd>
                                </div>
                            </dl>
                            @if($document->description)
                                <div class="mt-4">
                                    <dt class="text-sm font-medium text-gray-500 mb-1">Description</dt>
                                    <dd class="text-sm text-gray-900">{{ $document->description }}</dd>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Signature Areas -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">Signature Areas</h3>
                                @if($document->canBeEdited())
                                    <a href="{{ route('documents.map-areas', $document->uuid) }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                                        + Add Areas
                                    </a>
                                @endif
                            </div>

                            @if($document->signatureAreas->isEmpty())
                                <p class="text-gray-500 text-center py-4">No signature areas defined yet</p>
                            @else
                                <div class="space-y-3">
                                    @foreach($document->signatureAreas as $area)
                                        <div class="border border-gray-200 rounded-lg p-4">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <p class="font-medium text-gray-900">{{ $area->display_label }}</p>
                                                    <p class="text-sm text-gray-600">Page {{ $area->page_number }}</p>
                                                </div>
                                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium {{ $area->is_signed ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                                    {{ $area->is_signed ? 'Signed' : 'Unsigned' }}
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Signatures -->
                    @if($document->signatures->isNotEmpty())
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Signatures</h3>
                                <div class="space-y-3">
                                    @foreach($document->signatures as $signature)
                                        <div class="border border-gray-200 rounded-lg p-4">
                                            <div class="flex items-start space-x-3">
                                                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                </div>
                                                <div class="flex-1">
                                                    <p class="font-medium text-gray-900">{{ $signature->signer_name }}</p>
                                                    @if($signature->signer_job_title)
                                                        <p class="text-sm text-gray-600">{{ $signature->signer_job_title }}</p>
                                                    @endif
                                                    <p class="text-xs text-gray-500 mt-1">
                                                        Signed {{ $signature->signed_at->format('M d, Y h:i A') }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Actions -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions</h3>
                            <div class="space-y-2">
                                @if($document->canBeEdited())
                                    <a href="{{ route('documents.map-areas', $document->uuid) }}" class="w-full flex items-center justify-center px-4 py-2 border border-indigo-600 text-indigo-600 rounded-lg hover:bg-indigo-50">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        Map Signature Areas
                                    </a>
                                @endif

                                @if($document->canBeSigned())
                                    <a href="{{ route('documents.sign', $document->uuid) }}" class="w-full flex items-center justify-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                        </svg>
                                        Sign Document
                                    </a>
                                @endif

                                <a href="{{ route('documents.download', [$document->uuid, 'version' => 'original']) }}" class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                    </svg>
                                    Download Original
                                </a>

                                @if($document->status === 'signed' || $document->status === 'final')
                                    <a href="{{ route('documents.download', [$document->uuid, 'version' => 'signed']) }}" class="w-full flex items-center justify-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                        </svg>
                                        Download Signed
                                    </a>
                                @endif

                                @if($document->canBeDeleted())
                                    <form action="{{ route('documents.destroy', $document->uuid) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this document?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full flex items-center justify-center px-4 py-2 border border-red-300 text-red-700 rounded-lg hover:bg-red-50">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                            Delete Document
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Document Versions -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Versions</h3>
                            <div class="space-y-2">
                                @foreach($document->versions as $version)
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-gray-700">v{{ $version->version_number }} - {{ $version->version_type_label }}</span>
                                        <span class="text-gray-500">{{ $version->file_size_human }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
