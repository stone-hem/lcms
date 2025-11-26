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
        Schema::create('legal_case_notes', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('legal_case_id')->nullable(false);
            $table->foreign('legal_case_id')->references('id')->on('legal_cases')->cascadeOnDelete();

            $table->unsignedBigInteger('created_by')->nullable(false);
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();

            $table->string("title", 1000)->nullable(true);
            $table->text("note")->nullable(false);

            $table->timestamp("date");
            $table->json("attachments")->nullable(true);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('legal_case_notes');
    }
};
