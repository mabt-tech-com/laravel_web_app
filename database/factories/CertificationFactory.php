<?php

namespace Database\Factories;

use App\Models\Certification;
use App\Models\Quiz;
use App\Models\Role;
use App\Models\Training;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Certification>
 */
class CertificationFactory extends Factory
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
            'training_id' => Training::all()->random()->id,
            'quiz_id' => Quiz::all()->random()->id,
        ];
    }
}
