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
        Schema::create('legal_case_activity_participants', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('legal_case_activity_id')->nullable(true);
            $table->foreign('legal_case_activity_id')->references('id')->on('legal_case_activities')->cascadeOnDelete();

            $table->unsignedBigInteger('lawyer_id')->nullable(true);
            $table->foreign('lawyer_id')->references('id')->on('users')->cascadeOnDelete();

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('legal_case_activity_participants');
    }
};
