<?php

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
        Schema::create('case_activities', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            // $table->foreignIdFor(LegalCase::class)->constrained();
            $table->json("fields")->nullable(true);
            // $table->json("values")->nullable(true);
            $table->integer("position")->nullable(false)->default(0);
            $table->text("description")->nullable(true);

            $table->unsignedBigInteger('after')->nullable(true);
            $table->foreign('after')->references('id')->on('case_activities')->onDelete("set null");
            // $table->boolean("is_done")->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('case_activities');
    }
};
