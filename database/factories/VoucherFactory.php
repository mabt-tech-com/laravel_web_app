<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Quiz;
use App\Models\Training;
use App\Models\Voucher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Voucher>
 */
class VoucherFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::all()->random()->id,
            'code' => fake()->swiftBicNumber(),
            'title' => fake()->sentence(),
            'description' => fake()->sentence(),
            'active' => true,
            'expires_at' => '2024-06-30 23:59:59',
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Voucher $voucher) {
            $voucher->trainings()->sync(Training::inRandomOrder()->limit(3)->pluck('id'));
            $voucher->quizzes()->sync(Quiz::inRandomOrder()->limit(3)->pluck('id'));
        });
    }
}
