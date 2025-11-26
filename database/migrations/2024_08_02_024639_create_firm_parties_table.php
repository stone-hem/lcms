<?php

use App\Models\LegalCase\LegalCase;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Party\PartyType;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('firm_parties', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('calling_code')->nullable();
            $table->string('phone');
            $table->string('email')->unique();
            $table->string('photo_url')->nullable();
            $table->text('physical_address')->nullable(true);
            $table->text('postal_address')->nullable(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('firm_parties');
    }
};
