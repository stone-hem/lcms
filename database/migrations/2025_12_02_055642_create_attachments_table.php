<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            
            $table->text('title');
            $table->text('file_name')->nullable();
            $table->text('file_path');
            $table->bigInteger('size')->nullable();
            $table->text('extension')->nullable();
            $table->text('mime_type')->nullable();
            
            $table->morphs('attachable');
          
          
          
            $table->boolean('is_folder')->default(false);
          
            $table->text('notes')->nullable();
          
            $table->boolean('is_verified')->default(false);
            $table->timestampTz('verified_at')->nullable();
          
            $table->text('ocr_text')->nullable();
            $table->jsonb('metadata')->nullable();

            $table->text('status')->default('active');
          
            
          
            // Foreign Keys
            $table->foreignId('document_type_id')->nullable()->references('id')->on('document_types')->nullOnDelete();
          
            $table->foreignId('created_by')->references('id')->on('users')->restrictOnDelete();
            $table->foreignId('parent_id')->references('id')->on('files')->cascadeOnDelete();
          
            $table->foreignId('verified_by_id')->nullable()->references('id')->on('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
