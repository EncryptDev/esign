<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('barcode_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('token', 64)->unique();
            $table->foreignId('signature_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('document_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('barcode_type', 50);
            $table->text('barcode_data');
            $table->text('verification_url');
            $table->boolean('is_valid')->default(true);
            $table->unsignedInteger('verified_count')->default(0);
            $table->timestamp('last_verified_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('token');
            $table->index('signature_id');
            $table->index('document_id');
            $table->index('is_valid');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('barcode_tokens');
    }
};
