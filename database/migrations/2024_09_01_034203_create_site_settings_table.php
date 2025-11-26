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
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('tagline')->nullable(true);
            $table->json('about_us')->nullable(true)->comment('ie[{"mission":"xxxx"},{"vision":"xxxx"},{"Core values":"xxxx"},{"profile":"xxx"},{"history":"xxx"}]');
            $table->longText('privacy_policy')->nullable(true);
            $table->longText('return_policy')->nullable(true);
            $table->longText('terms_and_conditions')->nullable(true);

            $table->json('phones')->nullable(true)->comment('ie [{"finance":"0791507732"},{"administration":"0791507732"}]');
            $table->json('emails')->nullable(true)->comment('ie [{"finance":"sample@finance.com"},{"administration":"sample@administration.com"}]');
            $table->string('address_line_1')->nullable(true);
            $table->string('address_line_2')->nullable(true);
            $table->string('lat')->nullable(true);
            $table->string('lng')->nullable(true);
            $table->string('city')->nullable(true);
            $table->string('state')->nullable(true);
            $table->string('zip')->nullable(true);
            $table->string('country')->nullable(true);

            $table->text('app_store_logo_path')->nullable(true);
            $table->text('google_play_store_logo_path')->nullable(true);
            
            $table->text('site_logo_path')->nullable(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
