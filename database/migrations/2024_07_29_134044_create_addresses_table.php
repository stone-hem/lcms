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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->string('type')->nullable();
            $table->string('address_line')->nullable();
            $table->string('town')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('postal_address')->nullable();
            $table->string('sub_code')->nullable();
            $table->string('county_code')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
