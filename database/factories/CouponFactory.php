<?php

namespace Database\Factories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Coupon>
 */
class CouponFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fake()->swiftBicNumber(),
            'title' => fake()->sentence(),
            'description' => fake()->sentence(),
            'discount_percentage' => fake()->numberBetween(10, 80),
            'discount_value' => fake()->randomElement([10, 20, 50, 100]),
            'applicable_if_total_is_above' => fake()->randomElement([100, 500, 1000]),
            'max_usage' => fake()->numberBetween(10, 20),
            'active' => true,
            'starts_at' => now(),
            'expires_at' => '2024-06-30 23:59:59',
        ];
    }
}
