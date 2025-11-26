<?php

use App\Models\LegalCase\CaseSLA;
use App\Models\LegalCase\CaseSLAPayables;
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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('case_id')->nullable(true);
            $table->foreign('case_id')->references('id')->on('legal_cases')->onDelete('cascade');

            $table->unsignedBigInteger('contingent_liability_id')->nullable(true);
            $table->foreign('contingent_liability_id')->references('id')->on('contingent_liabilities')->onDelete('cascade');

            $table->unsignedBigInteger('case_sla_id')->nullable(true);
            $table->foreign('case_sla_id')->references('id')->on('case_slas')->onDelete('cascade');

            
            $table->string('method')->nullable(false);
            $table->string('amount')->nullable(false);          
            $table->string('ref')->nullable(true);
        
            $table->string('receipt_no')->nullable(true);
            $table->timestamp('transaction_date')->nullable(true);
            $table->string('msisdn_idnum')->nullable(true);
            $table->string('card_mask')->nullable(true);
            $table->string('merchant_request_id')->nullable();
            $table->string('mpesa_result_code')->nullable();
            $table->string('mpesa_result_desc')->nullable();
            $table->string('checkout_request_id')->nullable();
            $table->string('ipay_status')->nullable(true);
            
            $table->boolean('manually_recorded')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
