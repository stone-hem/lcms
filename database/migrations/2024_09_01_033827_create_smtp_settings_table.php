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
        Schema::create('smtp_settings', function (Blueprint $table) {
            $table->id();
            $table->string('smtp_host');
            $table->integer('smtp_port');
            $table->string('smtp_encryption');
            $table->string('smtp_username');
            $table->string('smtp_password');
            $table->string('mail_from');

            $table->string('reply_to')->nullable(true);
            $table->unsignedBigInteger('type_id')->nullable(true);
            $table->foreign('type_id')->references('id')->on('smtp_config_types')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('smtp_settings');
    }
};
