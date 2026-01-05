<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'e-Signing System') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-gradient-to-br from-blue-50 to-indigo-100">
    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <header class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex justify-between items-center">
                    <div class="flex items-center">
                        <svg class="w-10 h-10 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                        <span class="ml-3 text-2xl font-bold text-gray-900">PT Encrypt Digital Solution</span>
                    </div>
                    <div class="flex items-center space-x-4">
                        @auth
                            <a href="{{ route('dashboard') }}" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 font-medium">Go to Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 font-medium">Employee Login</a>
                        @endauth
                    </div>
                </div>
            </div>
        </header>

        <!-- Hero Section -->
        <main class="flex-1">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
                <div class="text-center">
                    <h1 class="text-5xl font-bold text-gray-900 mb-6">
                        Digital Signature Solution<br/>
                        <span class="text-indigo-600">by PT Encrypt Digital Solution</span>
                    </h1>
                    <p class="text-xl text-gray-600 mb-12 max-w-3xl mx-auto">
                        Professional e-Signing platform with advanced barcode verification for PT Encrypt Digital Solution.
                        Secure, efficient, and legally compliant document signing system for internal use.
                    </p>
                    <div class="flex justify-center space-x-4">
                        @auth
                            <a href="{{ route('dashboard') }}" class="bg-indigo-600 text-white px-8 py-4 rounded-lg hover:bg-indigo-700 font-semibold text-lg shadow-lg">
                                Go to Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="bg-indigo-600 text-white px-8 py-4 rounded-lg hover:bg-indigo-700 font-semibold text-lg shadow-lg">
                                Employee Login
                            </a>
                        @endauth
                    </div>
                </div>

                <!-- Features Grid -->
                <div class="mt-24 grid md:grid-cols-3 gap-8">
                    <!-- Feature 1 -->
                    <div class="bg-white p-8 rounded-xl shadow-lg hover:shadow-xl transition">
                        <div class="w-16 h-16 bg-indigo-100 rounded-lg flex items-center justify-center mb-6">
                            <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Easy Upload</h3>
                        <p class="text-gray-600">Upload your PDF documents securely. Support for multi-page documents with instant preview.</p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="bg-white p-8 rounded-xl shadow-lg hover:shadow-xl transition">
                        <div class="w-16 h-16 bg-indigo-100 rounded-lg flex items-center justify-center mb-6">
                            <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Digital Signature</h3>
                        <p class="text-gray-600">Place signature stamps with embedded QR codes. Cryptographically secure and tamper-proof.</p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="bg-white p-8 rounded-xl shadow-lg hover:shadow-xl transition">
                        <div class="w-16 h-16 bg-indigo-100 rounded-lg flex items-center justify-center mb-6">
                            <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Public Verification</h3>
                        <p class="text-gray-600">Anyone can verify signatures by scanning QR codes. View signer details and signing timestamp.</p>
                    </div>
                </div>

                <!-- How It Works -->
                <div class="mt-24">
                    <h2 class="text-3xl font-bold text-center text-gray-900 mb-12">How It Works</h2>
                    <div class="grid md:grid-cols-4 gap-8">
                        <div class="text-center">
                            <div class="w-16 h-16 bg-indigo-600 text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">1</div>
                            <h4 class="font-semibold text-gray-900 mb-2">Register Account</h4>
                            <p class="text-gray-600 text-sm">Create your free account in seconds</p>
                        </div>
                        <div class="text-center">
                            <div class="w-16 h-16 bg-indigo-600 text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">2</div>
                            <h4 class="font-semibold text-gray-900 mb-2">Upload Document</h4>
                            <p class="text-gray-600 text-sm">Upload your PDF document</p>
                        </div>
                        <div class="text-center">
                            <div class="w-16 h-16 bg-indigo-600 text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">3</div>
                            <h4 class="font-semibold text-gray-900 mb-2">Add Signature</h4>
                            <p class="text-gray-600 text-sm">Place signature areas and sign</p>
                        </div>
                        <div class="text-center">
                            <div class="w-16 h-16 bg-indigo-600 text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-4">4</div>
                            <h4 class="font-semibold text-gray-900 mb-2">Verify Anytime</h4>
                            <p class="text-gray-600 text-sm">Share and verify with QR code</p>
                        </div>
                    </div>
                </div>

                <!-- Stats -->
                <div class="mt-24 bg-white rounded-2xl shadow-xl p-12">
                    <div class="grid md:grid-cols-4 gap-8 text-center">
                        <div>
                            <div class="text-4xl font-bold text-indigo-600 mb-2">100%</div>
                            <div class="text-gray-600">Secure</div>
                        </div>
                        <div>
                            <div class="text-4xl font-bold text-indigo-600 mb-2">Free</div>
                            <div class="text-gray-600">Personal Use</div>
                        </div>
                        <div>
                            <div class="text-4xl font-bold text-indigo-600 mb-2">Fast</div>
                            <div class="text-gray-600">Instant Signing</div>
                        </div>
                        <div>
                            <div class="text-4xl font-bold text-indigo-600 mb-2">24/7</div>
                            <div class="text-gray-600">Verification</div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200 py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center text-gray-600">
                    <p>&copy; {{ date('Y') }} PT Encrypt Digital Solution. All rights reserved.</p>
                    <p class="mt-2 text-sm">Enterprise Digital Signature Platform</p>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>
