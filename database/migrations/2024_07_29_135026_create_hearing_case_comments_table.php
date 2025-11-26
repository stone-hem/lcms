<?php

use App\Models\LegalCase\CaseHearing;
use App\Models\LegalCase\LegalCase;
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
        Schema::create('hearing_case_comments', function (Blueprint $table) {
            $table->id();
            $table->text('comment');
            $table->foreignIdFor(\App\Models\User::class)->constrained();
            $table->foreignIdFor(LegalCase::class)->constrained();
            $table->foreignIdFor(CaseHearing::class)->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hearing_case_comments');
    }
};
