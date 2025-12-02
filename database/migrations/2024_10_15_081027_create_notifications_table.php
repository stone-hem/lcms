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
            $table->morphs('notifiable');
            $table->longText("message")->nullable(true);
            $table->longText("subject")->nullable(true);


            $table->timestamp('activity_date')->nullable(true);
            $table->timestamp('notification_date')->nullable(true);

            $table->timestamp('sent_at')->nullable(true);
            $table->timestamp('read_at')->nullable(true);

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
