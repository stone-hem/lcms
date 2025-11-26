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
        Schema::create('case_expenses', function (Blueprint $table) {
            $table->id();
            $table->timestamp("date_of_entry")->nullable(false);
            $table->text("quantity")->nullable(false);
            $table->text("unit_price")->nullable(false);
            $table->text("cost")->nullable(false);
            $table->text("description")->nullable(false);
        
            $table->unsignedBigInteger('expense_type_id')->nullable(true);
            $table->foreign('expense_type_id')->references('id')->on('expense_types')->nullOnDelete();

            $table->unsignedBigInteger('case_id')->nullable(false);
            $table->foreign('case_id')->references('id')->on('legal_cases')->cascadeOnDelete();

            $table->json("files")->nullable(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('case_expenses');
    }
};
