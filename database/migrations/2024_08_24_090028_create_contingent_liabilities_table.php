<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\LegalCase\LegalCase;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('contingent_liabilities', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('legal_case_id');
            $table->foreign('legal_case_id')->references('id')->on('legal_cases')->cascadeOnDelete();

            $table->double('amount');
            $table->string('description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contingent_liabilities');
    }
};
