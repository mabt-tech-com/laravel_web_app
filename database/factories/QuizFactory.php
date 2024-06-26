<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Chapter;
use App\Models\Quiz;
use App\Models\Training;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Quiz>
 */
class QuizFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'training_id' => fake()->randomElement([true, false]) ? null : Training::all()->random()->id,
            'chapter_id' => fake()->randomElement([true, false]) ? null : Chapter::all()->random()->id,
            'label' => fake()->country(),
            'description' => fake()->sentence(),
            'duration' => fake()->numberBetween(30, 1000),
            'break_interval' => fake()->randomElement([5, 10, 15, 20]),
            'break_duration_in_mins' => fake()->randomElement([5, 10, 15, 20]),
            'max_attempts' => fake()->randomElement([3, 5, 10]),
            'passing_percentage' => fake()->randomElement([60, 70, 80, 90]),
            'is_published' => fake()->randomElement([0, 1]),
            'price' => fake()->randomElement([60, 70, 80, 90]),
            'discounted_price' => null,
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Quiz $quiz) {
            $quiz->categories()->sync(Category::inRandomOrder()->limit(3)->pluck('id'));
        });
    }
}
