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
        Schema::create('parties', function (Blueprint $table) {
            $table->id();

                     // individual | firm
                     $table->enum('party_kind', ['individual', 'firm']);
                     $table->text('name')->nullable(true);


                     // universal
                     $table->string('calling_code')->nullable();
                     $table->string('phone');
                     $table->string('email')->unique();
                     $table->string('photo_url')->nullable();
                     $table->text('physical_address')->nullable();
                     $table->text('postal_address')->nullable();

                     // individual
                     $table->string('first_name')->nullable();
                     $table->string('middle_name')->nullable();
                     $table->string('last_name')->nullable();
                     $table->string('gender')->nullable();
                     $table->string('birth_date')->nullable();
                     
                     $table->text('bank_name')->nullable(true);
                     $table->text('bank_branch')->nullable(true);
                     $table->text('bank_account_number')->nullable(true);
                     $table->text('meta')->nullable(true);
         
         
                     $table->foreignId('user_id')->nullable()->references('id')->on('users')->nullOnDelete();
                     $table->text('kra_pin')->nullable(true);


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parties');
    }
};
