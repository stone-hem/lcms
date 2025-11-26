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
        Schema::create('document_templates', function (Blueprint $table) {
            $table->id();
            $table->string('title', 1000);
            $table->text('description')->nullable(true);

            $table->unsignedBigInteger('document_type_id')->nullable(true);
            $table->foreign('document_type_id')->references('id')->on('document_types')->onDelete('cascade');
            $table->json("templates");
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_templates');
    }
};
