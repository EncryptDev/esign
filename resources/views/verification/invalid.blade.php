<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invalid Verification - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="px-6 py-8 bg-red-50 border-b border-red-100">
                    <div class="flex justify-center mb-4">
                        <svg class="w-16 h-16 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-center text-red-900">Invalid Verification Code</h1>
                </div>

                <div class="px-6 py-8">
                    <div class="text-center mb-6">
                        <p class="text-gray-700 mb-4">
                            The verification code you scanned is invalid or corrupted.
                        </p>
                        <p class="text-sm text-gray-600">
                            Error: <span class="font-mono text-red-600">{{ $error ?? 'Invalid token format' }}</span>
                        </p>
                    </div>

                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                        <div class="flex">
                            <svg class="w-5 h-5 text-yellow-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            <div class="text-sm text-yellow-800">
                                <p class="font-semibold mb-1">Possible reasons:</p>
                                <ul class="list-disc list-inside space-y-1">
                                    <li>The QR code is damaged or incomplete</li>
                                    <li>The verification link was modified</li>
                                    <li>The code format is not recognized</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="text-center">
                        <p class="text-sm text-gray-600 mb-4">
                            Please contact the issuer for a valid verification code.
                        </p>
                        <a href="{{ route('login') }}"
                           class="inline-block bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 font-medium">
                            Go to Login
                        </a>
                    </div>
                </div>

                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 text-center">
                    <p class="text-xs text-gray-500">
                        Powered by {{ config('company.name') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
