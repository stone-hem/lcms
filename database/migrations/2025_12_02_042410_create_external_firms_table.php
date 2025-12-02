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
        Schema::create('external_firms', function (Blueprint $table) {
            $table->id();
                    $table->string('firm_name');
                    $table->string('bank_name');
                    $table->string('bank_branch')->nullable();
                    $table->string('bank_account_number');
                    $table->string('postal_address');
                    $table->string('kra_pin');
                    $table->string('email')->unique()->nullable(); // only if can login
                    $table->string('phone')->nullable();
                    $table->string('physical_address')->nullable();
                    $table->string('website')->nullable();
                    $table->text('notes')->nullable();
                    $table->boolean('can_login')->default(false);
                    $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
                    $table->timestamps();
                    $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('external_firms');
    }
};
