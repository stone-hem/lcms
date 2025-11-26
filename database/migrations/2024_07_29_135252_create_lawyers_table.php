<?php

use App\Models\Document\LawyerType;
use App\Models\Shared\Address;
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
        Schema::create('lawyers', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('gender',50);
            $table->date('birth_date');
            $table->string('email');
            $table->string('calling_code');
            $table->string('phone');
            $table->boolean('is_internal');
            $table->string('photo_url')->nullable();
            // $table->foreignIdFor(Address::class)->constrained();
            $table->foreignIdFor(\App\Models\User::class)->constrained();
            $table->foreignIdFor(LawyerType::class)->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lawyers');
    }
};
