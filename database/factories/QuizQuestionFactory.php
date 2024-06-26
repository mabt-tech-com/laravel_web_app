<?php

namespace Database\Factories;

use App\Models\Quiz;
use App\Models\QuizQuestionType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Quiz>
 */
class QuizQuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'quiz_id' => Quiz::all()->random()->id,
            'quiz_question_type_id' => QuizQuestionType::all()->random()->id,
            'label' => fake()->paragraph() . ' ?',
            'tip' => fake()->sentence(),
            'explanation' => fake()->sentence(),
        ];
    }
}
