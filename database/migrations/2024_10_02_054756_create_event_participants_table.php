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
        Schema::create('event_participants', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('calender_event_id')->nullable(true);
            $table->foreign('calender_event_id')->references('id')->on('calender_events')->cascadeOnDelete();

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
        Schema::dropIfExists('event_participants');
    }
};
