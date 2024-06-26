<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Quiz;
use App\Models\Role;
use App\Models\Training;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'student_id' => User::where('role_id', Role::STUDENT)->get()->random()->id,
            'type' => Order::ORDER,
            'order_status_id' => OrderStatus::PENDING_ID,
            'coupon_id' => fake()->randomElement([true, false]) ? null : null, // Coupon::all()->random()->id,
            'voucher_id' => fake()->randomElement([true, false]) ? null : Voucher::all()->random()->id,
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Order $order) {
            $order->trainings()->sync(Training::inRandomOrder()->limit(3)->pluck('id'));
            $order->quizzes()->sync(Quiz::inRandomOrder()->limit(3)->pluck('id'));
        });
    }
}
