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
        Schema::create('legal_case_parties', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('party_id')
                            ->constrained('parties')
                            ->cascadeOnDelete();
            
            $table->foreignId('party_type_id')
                            ->nullable()
                            ->constrained('party_types')
                            ->cascadeOnDelete();
            
            $table->foreignId('legal_case_id')
                            ->constrained('legal_cases')
                            ->cascadeOnDelete();
            
              
     

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('legal_case_parties');
    }
};
