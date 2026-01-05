<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $qrCode->metadata['title'] ?? 'QR Code Details' }}
            </h2>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $qrCode->is_valid ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                {{ $qrCode->is_valid ? 'Active' : 'Revoked' }}
            </span>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- QR Code Preview -->
                <div class="lg:col-span-2">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">QR Code Preview</h3>

                            <div class="bg-gray-50 border-2 border-gray-200 rounded-lg p-8 text-center">
                                @if(Storage::exists($qrCode->metadata['barcode_path'] ?? ''))
                                    <img src="{{ Storage::url($qrCode->metadata['barcode_path']) }}"
                                         alt="QR Code"
                                         class="mx-auto max-w-sm">
                                @else
                                    <div class="py-12">
                                        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                                        </svg>
                                        <p class="text-gray-500">QR Code not available</p>
                                    </div>
                                @endif
                            </div>

                            <div class="mt-4 p-4 bg-blue-50 rounded-lg">
                                <p class="text-sm text-blue-800">
                                    <strong>💡 Usage:</strong> Download this QR code and embed it in your physical documents,
                                    ID cards, or certificates. Anyone can scan it to verify authenticity.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- QR Code Information -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">QR Code Information</h3>

                            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Title</dt>
                                    <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ $qrCode->metadata['title'] ?? 'N/A' }}</dd>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Purpose</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $qrCode->metadata['purpose'] ?? 'N/A' }}</dd>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                                    <dd class="mt-1">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $qrCode->is_valid ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $qrCode->verification_status }}
                                        </span>
                                    </dd>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Times Scanned</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $qrCode->verified_count }} times</dd>
                                </div>

                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Created</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $qrCode->created_at->format('M d, Y h:i A') }}</dd>
                                </div>

                                @if($qrCode->expires_at)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Valid Until</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $qrCode->expires_at->format('M d, Y') }}</dd>
                                </div>
                                @endif

                                @if($qrCode->last_verified_at)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Last Scanned</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $qrCode->last_verified_at->diffForHumans() }}</dd>
                                </div>
                                @endif
                            </dl>

                            @if($qrCode->metadata['description'])
                                <div class="mt-4 pt-4 border-t border-gray-200">
                                    <dt class="text-sm font-medium text-gray-500 mb-1">Description</dt>
                                    <dd class="text-sm text-gray-900">{{ $qrCode->metadata['description'] }}</dd>
                                </div>
                            @endif

                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <dt class="text-sm font-medium text-gray-500 mb-1">Verification URL</dt>
                                <dd class="text-xs text-gray-900 font-mono bg-gray-50 p-2 rounded break-all">
                                    {{ $qrCode->verification_url }}
                                </dd>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions Sidebar -->
                <div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg sticky top-6">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions</h3>

                            <div class="space-y-2">
                                <a href="{{ route('qrcodes.download', $qrCode->id) }}"
                                   class="w-full flex items-center justify-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                    </svg>
                                    Download QR Code
                                </a>

                                <a href="{{ $qrCode->verification_url }}"
                                   target="_blank"
                                   class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                    </svg>
                                    Test Verification
                                </a>

                                @if($qrCode->is_valid)
                                    <form action="{{ route('qrcodes.revoke', $qrCode->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to revoke this QR code?')">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit"
                                                class="w-full flex items-center justify-center px-4 py-2 border border-yellow-300 text-yellow-700 rounded-lg hover:bg-yellow-50 font-medium">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                            </svg>
                                            Revoke QR Code
                                        </button>
                                    </form>
                                @endif

                                <form action="{{ route('qrcodes.destroy', $qrCode->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this QR code? This action cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="w-full flex items-center justify-center px-4 py-2 border border-red-300 text-red-700 rounded-lg hover:bg-red-50 font-medium">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                        Delete QR Code
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
