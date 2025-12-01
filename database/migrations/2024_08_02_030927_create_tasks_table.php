<?php

use App\Models\CaseActivity;
use App\Models\Event;
use App\Models\Task;
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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(Event::class)->nullable()->constrained();
            $table->foreignIdFor(CaseActivity::class)->nullable()->constrained();
            $table->foreignIdFor(User::class)->nullable()->constrained();

            $table->unsignedBigInteger('legal_case_id')->nullable(true);
            $table->foreign('legal_case_id')->references('id')->on('legal_cases')->onDelete('cascade');

            $table->unsignedBigInteger('parent_id')->nullable(true);
            $table->foreign('parent_id')->references('id')->on('tasks')->onDelete('cascade');

            $table->string('title');
            $table->json("tags")->nullable(true);
            $table->text('description');
            $table->dateTime('start_datetime');
            $table->dateTime('end_datetime');
            $table->integer('priority');
            $table->json('faved_by')->nullable(true);
            $table->integer('status')->comment('1=>pending,2=>in_progress,3=>completed,4=>cancelled');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
