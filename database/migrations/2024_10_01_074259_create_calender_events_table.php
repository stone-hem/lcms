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
        Schema::create('calender_events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamp('start_date');
            $table->timestamp('end_date');

            $table->unsignedBigInteger('legal_case_id')->nullable(true);
            $table->foreign('legal_case_id')->references('id')->on('legal_cases')->cascadeOnDelete();

            $table->unsignedBigInteger('added_by');
            $table->foreign('added_by')->references('id')->on('users')->cascadeOnDelete();


            $table->boolean('alert')->default(true);
            $table->boolean('is_public')->default(false);

            $table->json("attachments")->nullable(true);


            $table->integer('priority')->default(1);
            $table->unsignedBigInteger('category_id')->nullable(true);
            $table->foreign('category_id')->references('id')->on('event_categories')->cascadeOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calender_events');
    }
};
