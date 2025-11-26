<?php

use App\Models\LegalCase\LegalCase;
use App\Models\User;
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
        Schema::create('case_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(LegalCase::class)->cascadeOnDelete();
            $table->foreignIdFor(User::class)->cascadeOnDelete();
            $table->string('identifier')->nullable(true); // ie sla, dg_approval, procurement, etc
            $table->json('files_meta')->nullable(true);
            $table->unsignedBigInteger('foreign')->nullable(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('case_attachments');
    }
};
