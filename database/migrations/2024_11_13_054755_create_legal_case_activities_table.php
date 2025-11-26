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
        Schema::create('legal_case_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('legal_case_id')->nullable(false);
            $table->foreign('legal_case_id')->references('id')->on('legal_cases')->cascadeOnDelete();
            $table->unsignedBigInteger('case_activity_id')->nullable(false);
            $table->foreign('case_activity_id')->references('id')->on('case_activities')->cascadeOnDelete();

            $table->unsignedBigInteger('created_by')->nullable(false);
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();

            $table->string("title", 1000)->nullable(true);
            $table->text("description")->nullable(true);
            $table->timestamp("date");
            $table->json("fields")->nullable(true);
            $table->json("attachments")->nullable(true);
            $table->integer("status")->nullable(false)->default(1)->comment("1:pending 2:completed");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('legal_case_activities');
    }
};
