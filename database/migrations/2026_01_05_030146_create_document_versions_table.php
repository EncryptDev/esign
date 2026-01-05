<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_versions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('document_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('version_number');
            $table->enum('version_type', ['original', 'signed', 'revised']);
            $table->string('storage_path', 500);
            $table->unsignedBigInteger('file_size');
            $table->string('checksum', 64);
            $table->json('metadata')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['document_id', 'version_number']);
            $table->index('uuid');
            $table->index(['document_id', 'version_number']);
            $table->index('version_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_versions');
    }
};
