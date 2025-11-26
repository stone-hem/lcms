<?php

use App\Models\LegalCase\LegalCase;
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
        Schema::create('legal_case_interim_fee_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(LegalCase::class)->cascadeOnDelete();
            $table->double('amount');
            $table->boolean("is_paid")->default(false);
            $table->double('paid_amount')->default(0)->nullable(true);
            $table->double('balance')->default(0)->nullable(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('legal_case_interim_fee_notes');
    }
};
