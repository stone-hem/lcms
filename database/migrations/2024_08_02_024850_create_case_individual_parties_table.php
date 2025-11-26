<?php

use App\Models\IndividualParty;
use App\Models\LegalCase\LegalCase;
use App\Models\Party\PartyType;
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
        Schema::create('case_individual_parties', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(IndividualParty::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(PartyType::class)->constrained()->nullable()->cascadeOnDelete();
            $table->foreignIdFor(LegalCase::class)->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('case_individual_parties');
    }
};
