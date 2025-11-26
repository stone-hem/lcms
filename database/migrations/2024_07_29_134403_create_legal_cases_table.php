<?php

use App\Models\LegalCase\CaseType;
use App\Models\CaseStage;
use App\Models\LegalCase\NatureOfClaim;
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
        Schema::create('legal_cases', function (Blueprint $table) {
            $table->id();
            // $table->integer('status');
            $table->string('serial_number');
            $table->string('title');
            $table->string('case_number')->nullable(true)->unique();
            $table->boolean('is_internal')->default(true);
            
            $table->timestamp('date_received')->nullable(true);
            $table->timestamp('date_of_filing')->nullable(true);
            $table->timestamp('completion_date')->nullable(true);

            $table->string('court_name')->nullable(true);
            $table->string('year')->nullable(true);
            $table->text('description')->nullable(true);
            $table->foreignIdFor(\App\Models\User::class)->constrained();
            $table->foreignIdFor(CaseType::class)->nullable()->constrained()->nullOnDelete();;
            $table->foreignIdFor(CaseStage::class)->nullable()->constrained();
            $table->foreignIdFor(NatureOfClaim::class)->nullable()->constrained()->nullOnDelete();
            
            $table->unsignedBigInteger('lawyer_id')->nullable(true);
            $table->foreign('lawyer_id')->references('id')->on('users')->nullOnDelete();

            $table->json("activities")->nullable(true);
            $table->json("notes")->nullable(true);
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('legal_cases');
    }
};
