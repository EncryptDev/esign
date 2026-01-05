<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\SignatureAreaController;
use App\Http\Controllers\QRCodeGeneratorController;

// Public routes
Route::get('/', function () {
    return view('welcome');
});

Route::get('/verify/{token}', [VerificationController::class, 'show'])
    ->name('verification.show');
Route::get('/verify/{token}/download', [VerificationController::class, 'downloadDocument'])
    ->name('verification.download');

// Authentication routes (from Breeze)
require __DIR__ . '/auth.php';

// Protected routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Document routes
    Route::prefix('documents')->name('documents.')->group(function () {
        Route::get('/', [DocumentController::class, 'index'])->name('index');
        Route::get('/create', [DocumentController::class, 'create'])->name('create');
        Route::post('/', [DocumentController::class, 'store'])->name('store');
        Route::get('/{document:uuid}', [DocumentController::class, 'show'])->name('show');
        Route::get('/{document:uuid}/map-areas', [DocumentController::class, 'mapAreas'])->name('map-areas');
        Route::get('/{document:uuid}/sign', [DocumentController::class, 'signPage'])->name('sign');
        Route::post('/{document:uuid}/sign', [DocumentController::class, 'processSign'])->name('process-sign');
        Route::get('/{document:uuid}/download', [DocumentController::class, 'download'])->name('download');
        Route::delete('/{document:uuid}', [DocumentController::class, 'destroy'])->name('destroy');
    });

    // PDF Preview for signature mapping
    Route::get('/documents/{document:uuid}/pdf-preview', [DocumentController::class, 'pdfPreview'])
        ->name('documents.pdf-preview');

    // Signature Areas API
    Route::post('/signature-areas', [SignatureAreaController::class, 'store'])
        ->name('signature-areas.store');
    Route::put('/signature-areas/{signatureArea}', [SignatureAreaController::class, 'update'])
        ->name('signature-areas.update');
    Route::delete('/signature-areas/{signatureArea}', [SignatureAreaController::class, 'destroy'])
        ->name('signature-areas.destroy');

    // QR Code Generator Routes
    Route::prefix('qrcodes')->name('qrcodes.')->group(function () {
        Route::get('/', [QRCodeGeneratorController::class, 'index'])->name('index');
        Route::get('/create', [QRCodeGeneratorController::class, 'create'])->name('create');
        Route::post('/', [QRCodeGeneratorController::class, 'store'])->name('store');
        Route::get('/{id}', [QRCodeGeneratorController::class, 'show'])->name('show');
        Route::get('/{id}/download', [QRCodeGeneratorController::class, 'download'])->name('download');
        Route::put('/{id}/revoke', [QRCodeGeneratorController::class, 'revoke'])->name('revoke');
        Route::delete('/{id}', [QRCodeGeneratorController::class, 'destroy'])->name('destroy');
    });
});
