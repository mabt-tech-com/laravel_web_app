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
        Schema::create('quiz_question_options_items', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('quiz_question_option_id');
            $table->foreign('quiz_question_option_id')->references('id')->on('quiz_question_options')->onDelete('cascade');

            $table->text('label');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_question_options');
    }
};
