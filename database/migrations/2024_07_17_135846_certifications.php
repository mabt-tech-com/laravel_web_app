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
        Schema::create('certifications', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('student_id');
            $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade');

            $table->unsignedBigInteger('training_id')->nullable();
            $table->foreign('training_id')->references('id')->on('trainings')->onDelete('cascade');

            $table->unsignedBigInteger('quiz_id')->nullable();
            $table->foreign('quiz_id')->references('id')->on('quizzes')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certifications');
    }
};
