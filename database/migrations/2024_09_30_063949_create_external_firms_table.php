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
        Schema::create('external_firms', function (Blueprint $table) {
            $table->id();
            $table->string('firm_name');
            $table->text('bank_name')->nullable(true);
            $table->text('bank_branch');
            $table->text('bank_account_number');
            $table->text('postal_address');
            $table->text('kra_pin');


            $table->unsignedBigInteger('user_id')->nullable(true);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();


            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('external_firms');
    }
};
