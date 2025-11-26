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
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('module')->index();
            $table->string("sub_module",500)->nullable(true);
            $table->string('name');
            $table->string('description')->nullable();
            $table->jsonb('meta')->nullable();
            $table->string('guard_name'); // e.g., 'web' (or your custom 'web[module][action]' used in the seeder)
            $table->timestamps();
            
            $table->unique(['name', 'guard_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
