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
        Schema::create('quiz_student_answers', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('quiz_student_attempt_id');
            $table->foreign('quiz_student_attempt_id')->references('id')->on('quiz_student_attempts')->onDelete('cascade');

            $table->unsignedBigInteger('quiz_question_id');
            $table->foreign('quiz_question_id')->references('id')->on('quiz_questions')->onDelete('cascade');

            $table->unsignedBigInteger('quiz_question_option_id');
            $table->foreign('quiz_question_option_id')->references('id')->on('quiz_question_options')->onDelete('cascade');

            $table->unsignedBigInteger('quiz_question_option_item_id')->nullable();
            $table->foreign('quiz_question_option_item_id')->references('id')->on('quiz_question_option_items')->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_attempts_answers');
    }
};
