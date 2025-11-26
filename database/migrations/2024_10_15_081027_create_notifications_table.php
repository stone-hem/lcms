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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            //$table->unsignedBigInteger('external_advocate_id')->nullable(true);
            //$table->foreign('external_advocate_id')->references('id')->on('external_firms')->cascadeOnDelete();


            $table->unsignedBigInteger('user_id')->nullable(true);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();

            $table->unsignedBigInteger('task_id')->nullable(true);
            $table->foreign('task_id')->references('id')->on('tasks')->cascadeOnDelete();

            $table->unsignedBigInteger('event_id')->nullable(true);
            $table->foreign('event_id')->references('id')->on('calender_events')->cascadeOnDelete();

            $table->boolean('is_case_activity')->nullable(false)->default(false);

            $table->longText("message")->nullable(true);
            $table->longText("subject")->nullable(true);


            $table->timestamp('activity_date')->nullable(true);
            $table->timestamp('notification_date')->nullable(true);
            $table->timestamp('email_sent_at')->nullable(true);
            $table->timestamp('read_at')->nullable(true);
            $table->timestamp('sms_sent_at')->nullable(true);

            $table->string("type")->nullable(true);


            $table->json("content")->nullable(true);

            $table->longText("thread_lock_id")->nullable(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
