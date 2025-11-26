<?php

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
        Schema::create('nature_of_claims', function (Blueprint $table) {
            $table->id();
            $table->string("claim",500)->unique();
            $table->foreignIdFor(User::class)->nullable()->constrained()->nullOnDelete();
            $table->text("description")->nullable(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nature_of_claims');
    }
};
