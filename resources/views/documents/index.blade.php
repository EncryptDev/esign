<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('My Documents') }}
            </h2>
            <a href="{{ route('documents.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 font-medium">
                Upload Document
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('documents.index') }}"
                           class="px-4 py-2 rounded-lg {{ !$status ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                            All Documents
                        </a>
                        <a href="{{ route('documents.index', ['status' => 'draft']) }}"
                           class="px-4 py-2 rounded-lg {{ $status === 'draft' ? 'bg-gray-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                            Draft
                        </a>
                        <a href="{{ route('documents.index', ['status' => 'signed']) }}"
                           class="px-4 py-2 rounded-lg {{ $status === 'signed' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                            Signed
                        </a>
                        <a href="{{ route('documents.index', ['status' => 'final']) }}"
                           class="px-4 py-2 rounded-lg {{ $status === 'final' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                            Final
                        </a>
                    </div>
                </div>
            </div>

            <!-- Documents List -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($documents->isEmpty())
                        <div class="text-center py-12">
                            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">No documents found</h3>
                            <p class="text-gray-600 mb-4">Get started by uploading your first document</p>
                            <a href="{{ route('documents.create') }}" class="inline-flex items-center bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                                Upload Document
                            </a>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($documents as $document)
                                <div class="border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition">
                                    <div class="p-6">
                                        <div class="flex items-start justify-between mb-4">
                                            <div class="flex-1">
                                                <h3 class="font-semibold text-gray-900 mb-1 truncate">{{ $document->title }}</h3>
                                                <p class="text-sm text-gray-600">{{ $document->page_count }} page(s)</p>
                                            </div>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $document->status_color }}-100 text-{{ $document->status_color }}-800">
                                                {{ $document->status_label }}
                                            </span>
                                        </div>

                                        @if($document->description)
                                            <p class="text-sm text-gray-600 mb-4 line-clamp-2">{{ $document->description }}</p>
                                        @endif

                                        <div class="flex items-center text-sm text-gray-500 mb-4">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            {{ $document->created_at->diffForHumans() }}
                                        </div>

                                        <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                                            <a href="{{ route('documents.show', $document->uuid) }}" class="text-indigo-600 hover:text-indigo-800 font-medium text-sm">
                                                View Details →
                                            </a>
                                            @if($document->canBeSigned())
                                                <a href="{{ route('documents.sign', $document->uuid) }}" class="bg-indigo-600 text-white px-3 py-1 rounded text-sm hover:bg-indigo-700">
                                                    Sign Now
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
