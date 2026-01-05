<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PT Encrypt Signature Verification</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-50 antialiased">
    <div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="flex justify-center mb-4">
                    <svg class="w-16 h-16 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold text-gray-900">Digital Signature Verification</h1>
                <p class="mt-2 text-gray-600">Public verification of electronically signed document</p>
            </div>

            <!-- Verification Status Card -->
            <div class="bg-white shadow-lg rounded-lg overflow-hidden mb-6">
                <div class="px-6 py-4 bg-green-50 border-b border-green-100">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                                </path>
                            </svg>
                            <div>
                                <h2 class="text-xl font-semibold text-green-800">Signature Verified</h2>
                                <p class="text-sm text-green-700">This signature is authentic and valid</p>
                            </div>
                        </div>
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            {{ $validityBadge['text'] }}
                        </span>
                    </div>
                </div>

                <div class="px-6 py-6">
                    <!-- Document Information -->
                    @if ($document)
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                                Document Information
                            </h3>
                            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Document Title</dt>
                                    <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ $documentTitle }}</dd>
                                </div>
                                @if ($documentPurpose)
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Purpose</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $documentPurpose }}</dd>
                                    </div>
                                @endif
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Document Status</dt>
                                    <dd class="mt-1">
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $statusBadge['color'] }}-100 text-{{ $statusBadge['color'] }}-800">
                                            {{ $statusBadge['text'] }}
                                        </span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Document ID</dt>
                                    <dd class="mt-1 text-sm text-gray-900 font-mono">
                                        {{ substr($documentUuid, 0, 8) }}...</dd>
                                </div>
                            </dl>
                        </div>

                        <div class="border-t border-gray-200 my-6"></div>
                    @else
                        <!-- Standalone QR Code Information -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z">
                                    </path>
                                </svg>
                                Verification Information
                            </h3>
                            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Title</dt>
                                    <dd class="mt-1 text-sm text-gray-900 font-semibold">
                                        {{ $barcodeToken->metadata['title'] ?? 'Verification QR Code' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Purpose</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ $barcodeToken->metadata['purpose'] ?? 'N/A' }}</dd>
                                </div>
                                @if (isset($barcodeToken->metadata['description']))
                                    <div class="sm:col-span-2">
                                        <dt class="text-sm font-medium text-gray-500">Description</dt>
                                        <dd class="mt-1 text-sm text-gray-900">
                                            {{ $barcodeToken->metadata['description'] }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>

                        <div class="border-t border-gray-200 my-6"></div>
                    @endif
                    <div class="border-t border-gray-200 my-6"></div>

                    <!-- Signer Information -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Signer Information
                        </h3>
                        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Name</dt>
                                <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ $signerName }}</dd>
                            </div>
                            @if ($signerJobTitle)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Job Title</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $signerJobTitle }}</dd>
                                </div>
                            @endif
                            @if ($signerDepartment)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Department</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $signerDepartment }}</dd>
                                </div>
                            @endif
                            @if ($signerCompany)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Company</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $signerCompany }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>

                    <div class="border-t border-gray-200 my-6"></div>

                    <!-- Signature Metadata -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Signature Details
                        </h3>
                        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Signed On</dt>
                                <dd class="mt-1 text-sm text-gray-900 font-semibold">
                                    {{ $signedAt->format('F d, Y') }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Signed At</dt>
                                <dd class="mt-1 text-sm text-gray-900 font-semibold">
                                    {{ $signedAt->format('h:i A T') }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Verification Count</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $verificationCount }} times</dd>
                            </div>
                            @if ($expiresAt)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Valid Until</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $expiresAt->format('F d, Y') }}</dd>
                                </div>
                            @endif
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">Signature Hash</dt>
                                <dd class="mt-1 text-xs text-gray-900 font-mono bg-gray-50 p-2 rounded">
                                    {{ $signatureHash }}...
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Security Notice -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex">
                    <svg class="w-5 h-5 text-blue-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <h4 class="text-sm font-semibold text-blue-900 mb-1">Security Information</h4>
                        <p class="text-sm text-blue-800">
                            This document has been digitally signed using cryptographic security.
                            The signature cannot be forged or tampered with. This verification page
                            is publicly accessible and proves the authenticity of the signature.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center text-sm text-gray-500">
                <p>Verified at {{ now()->format('F d, Y h:i A T') }}</p>
                <p class="mt-2">
                    Powered by {{ config('app.name') }} Digital Signature System
                </p>
            </div>
        </div>
    </div>
</body>

</html>
