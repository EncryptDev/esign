<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('signatures', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('document_id')->constrained()->onDelete('cascade');
            $table->foreignId('signature_area_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('signed_at');
            $table->string('ip_address', 45);
            $table->text('user_agent');
            $table->string('signature_hash');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('uuid');
            $table->index('document_id');
            $table->index('user_id');
            $table->index('signed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signatures');
    }
};
