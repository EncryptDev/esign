<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Company Information
    |--------------------------------------------------------------------------
    */
    'name' => env('COMPANY_NAME', 'PT Encrypt Digital Solution'),
    'short_name' => env('COMPANY_SHORT_NAME', 'EncryptDev'),
    'address' => env('COMPANY_ADDRESS', 'Mojokerto, Indonesia'),
    'phone' => env('COMPANY_PHONE', '+62 851-7110-6025'),
    'email' => env('COMPANY_EMAIL', 'client@encryptdev.com'),
    'website' => env('COMPANY_WEBSITE', 'https://encryptdev.com'),

    /*
    |--------------------------------------------------------------------------
    | Company Logo Configuration
    |--------------------------------------------------------------------------
    |
    | Logo path untuk barcode embedding
    | Logo harus berukuran square (1:1 ratio) dan format PNG dengan background transparan
    | Recommended size: 200x200px atau 400x400px
    |
    */
    'logo' => [
        // Path relatif dari storage/app/
        'path' => env('COMPANY_LOGO_PATH', 'logos/company-logo.png'),

        // Path relatif dari public/
        'public_path' => env('COMPANY_LOGO_PUBLIC_PATH', 'images/company-logo.png'),

        // Default jika logo tidak ditemukan
        'fallback' => storage_path('app/logos/default-logo.png'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Signature Configuration
    |--------------------------------------------------------------------------
    */
    'signature' => [
        // Default signature area size (in mm)
        'default_width' => 60,
        'default_height' => 30,

        // Barcode settings
        'barcode_size' => 400, // pixels
        'logo_size_percentage' => 20, // 20% of barcode size
    ],
];
