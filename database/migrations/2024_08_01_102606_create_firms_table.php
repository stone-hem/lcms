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
        Schema::create('firms', function (Blueprint $table) {
            $table->id();
            $table->text('name')->nullable(true);
            $table->text('bank_name')->nullable(true);
            $table->text('bank_account')->nullable(true);
            $table->text('bank_branch')->nullable(true);
            $table->text('kra_pin')->nullable(true);
            $table->text('postal_address')->nullable(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('firms');
    }
};
