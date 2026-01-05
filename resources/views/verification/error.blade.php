<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Error - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <div class="px-6 py-8 bg-red-50 border-b border-red-100">
                    <div class="flex justify-center mb-4">
                        <svg class="w-16 h-16 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold text-center text-red-900">Verification Error</h1>
                    <p class="text-center text-red-700 mt-2">Something went wrong</p>
                </div>

                <div class="px-6 py-8">
                    <div class="text-center mb-6">
                        <p class="text-gray-700 mb-4">
                            An error occurred while verifying this code.
                        </p>
                        @if(isset($error))
                            <p class="text-sm text-gray-600">
                                Error: <span class="font-mono text-red-600">{{ $error }}</span>
                            </p>
                        @endif
                    </div>

                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6">
                        <div class="flex">
                            <svg class="w-5 h-5 text-gray-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div class="text-sm text-gray-700">
                                <p class="font-semibold mb-1">What to do:</p>
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Try scanning the QR code again</li>
                                    <li>Make sure you have a stable internet connection</li>
                                    <li>Contact the issuer if the problem persists</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="text-center">
                        <button onclick="window.location.reload()"
                                class="inline-block bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 font-medium mb-2">
                            Try Again
                        </button>
                        <p class="text-sm text-gray-500">
                            or contact support if the issue continues
                        </p>
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
