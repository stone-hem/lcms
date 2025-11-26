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
        Schema::create('individual_parties', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable(true);
            $table->string('calling_code')->nullable();
            $table->string('phone');
            $table->string('email')->unique();
            $table->string('gender')->nullable();
            $table->string('birth_date')->nullable();
            $table->string('photo_url')->nullable();

            //$client->physical_address = $clientData['physical_address'];
            //$client->postal_address = $clientData['postal_address'];

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
        Schema::dropIfExists('individual_parties');
    }
};
