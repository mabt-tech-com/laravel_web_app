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
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('company_id');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');

            $table->unsignedBigInteger('training_id')->nullable();
            $table->foreign('training_id')->references('id')->on('trainings')->onDelete('cascade');

            $table->unsignedBigInteger('chapter_id')->nullable();
            $table->foreign('chapter_id')->references('id')->on('chapters')->onDelete('cascade');

            $table->tinytext('label');
            $table->text('description')->nullable();

            $table->integer('max_attempts');

            $table->integer('passing_percentage')->default(75);

            $table->integer('duration');

            $table->integer('break_interval')->nullable();
            $table->integer('break_duration_in_mins')->nullable();

            $table->boolean('is_published')->default(true);

            $table->decimal('price')->nullable();
            $table->decimal('discounted_price')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
