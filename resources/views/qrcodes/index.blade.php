<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('QR Code Generator') }}
            </h2>
            <a href="{{ route('qrcodes.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 font-medium">
                Generate QR Code
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Info Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-gradient-to-r from-purple-500 to-indigo-600 text-white">
                    <h3 class="text-xl font-bold mb-2">Standalone QR Code Generator</h3>
                    <p class="text-purple-100">
                        Generate verification QR codes without uploading documents.
                        Perfect for physical stamps, ID cards, or certificates.
                    </p>
                </div>
            </div>

            <!-- QR Codes List -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Your QR Codes</h3>

                    @if($qrCodes->isEmpty())
                        <div class="text-center py-12">
                            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                            </svg>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">No QR codes yet</h3>
                            <p class="text-gray-600 mb-4">Generate your first verification QR code</p>
                            <a href="{{ route('qrcodes.create') }}" class="inline-flex items-center bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">
                                Generate QR Code
                            </a>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($qrCodes as $qrCode)
                                <div class="border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition">
                                    <div class="p-6">
                                        <div class="flex items-start justify-between mb-4">
                                            <div class="flex-1">
                                                <h3 class="font-semibold text-gray-900 mb-1">
                                                    {{ $qrCode->metadata['title'] ?? 'QR Code' }}
                                                </h3>
                                                <p class="text-sm text-gray-600">{{ $qrCode->metadata['purpose'] ?? 'N/A' }}</p>
                                            </div>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $qrCode->is_valid ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $qrCode->is_valid ? 'Active' : 'Revoked' }}
                                            </span>
                                        </div>

                                        @if($qrCode->metadata['description'])
                                            <p class="text-sm text-gray-600 mb-4 line-clamp-2">{{ $qrCode->metadata['description'] }}</p>
                                        @endif

                                        <div class="flex items-center text-sm text-gray-500 mb-4">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                            Scanned {{ $qrCode->verified_count }} times
                                        </div>

                                        <div class="flex items-center text-sm text-gray-500 mb-4">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            {{ $qrCode->created_at->diffForHumans() }}
                                        </div>

                                        <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                                            <a href="{{ route('qrcodes.show', $qrCode->id) }}" class="text-indigo-600 hover:text-indigo-800 font-medium text-sm">
                                                View Details →
                                            </a>
                                            <a href="{{ route('qrcodes.download', $qrCode->id) }}" class="bg-indigo-600 text-white px-3 py-1 rounded text-sm hover:bg-indigo-700">
                                                Download
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        <div class="mt-6">
                            {{ $qrCodes->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
