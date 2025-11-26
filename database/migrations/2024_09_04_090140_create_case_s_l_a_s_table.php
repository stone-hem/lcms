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
        Schema::create('case_slas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legal_case_id')->nullable(true);
            $table->foreign('legal_case_id')->references('id')->on('legal_cases')->onDelete('cascade');
            $table->double('amount');
            $table->double('paid_amount')->default(0)->nullable(true);
            $table->double('balance')->default(0)->nullable(true);

            $table->text('description')->nullable(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('case_s_l_a_s');
    }
};
