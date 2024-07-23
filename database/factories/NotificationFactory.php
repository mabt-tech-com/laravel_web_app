<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Notification;


/**
 * @extends Factory<Notification>
 */
class NotificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = Notification::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence,
            'message' => $this->faker->paragraph,
            'user_id' => \App\Models\User::factory(),
            'role_id' => \App\Models\Role::factory(),
            'scheduled_at' => $this->faker->dateTime,
            'status' => 'pending',
            'send_via_smtp' => $this->faker->boolean,
            'read' => false,
            'archived' => false,
        ];
    }

}
