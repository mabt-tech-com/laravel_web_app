<?php

namespace Database\Factories;

use App\Models\QuizQuestion;
use App\Models\QuizQuestionOption;
use App\Models\QuizQuestionType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Quiz>
 */
class QuizQuestionOptionItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quiz_question_id = QuizQuestion::where('quiz_question_type_id', QuizQuestionType::DRAG_AND_DROP_ID)->get()->random()->id;
        $quiz_question_options_is_not_empty = QuizQuestion::with('quiz_question_options')->findOrFail($quiz_question_id)->quiz_question_options->isNotEmpty();

        if ($quiz_question_options_is_not_empty) {
            $quiz_question_option_id = QuizQuestion::with('quiz_question_options')->findOrFail($quiz_question_id)->quiz_question_options->random()->id;
        } else {
            $quiz_question_option_id = QuizQuestionOption::all()->random()->id;
        }

        return [
            'quiz_question_option_id' => $quiz_question_option_id,
            'label' => fake()->sentence(),
        ];
    }
}
