<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\File;
use App\Models\Role;
use App\Models\Tag;
use App\Models\Training;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Training>
 */
class TrainingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $user_id = User::where('role_id', Role::INSTRUCTOR)->get()->random()->id;

        return [
            'company_id' => 1,
            'instructor_id' => $user_id,
            'label' => fake()->country(),
            'description' => fake()->sentence(),
            'level' => fake()->randomElement([Training::BEGINNER, Training::INTERMEDIATE, Training::EXPERT]),
            'duration' => fake()->randomNumber(2) . ' Hrs ' . fake()->numberBetween(10, 59) . ' mins',
            'price' => fake()->numberBetween(10, 999),
            'discounted_price' => null,
            'image_id' => File::where('user_id', $user_id)->exists() ? File::where('user_id', $user_id)->get()->random()->id : null,
            'video_id' => null,
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Training $training) {
            $training->categories()->sync(Category::inRandomOrder()->limit(3)->pluck('id'));
            $training->tags()->sync(Tag::inRandomOrder()->limit(3)->pluck('id'));
        });
    }
}
