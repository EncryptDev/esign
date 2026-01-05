<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('signature_areas', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('document_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('page_number');
            $table->decimal('position_x', 10, 2);
            $table->decimal('position_y', 10, 2);
            $table->decimal('width', 10, 2);
            $table->decimal('height', 10, 2);
            $table->string('label')->nullable();
            $table->boolean('is_signed')->default(false);
            $table->timestamps();

            $table->index('uuid');
            $table->index(['document_id', 'page_number']);
            $table->index('is_signed');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signature_areas');
    }
};
