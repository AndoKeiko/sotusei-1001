<?php

namespace Database\Factories;

use App\Models\Goal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class GoalFactory extends Factory
{
    protected $model = Goal::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->sentence(3),
            'current_status' => $this->faker->paragraph(),
            'period_start' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'period_end' => $this->faker->dateTimeBetween('now', '+1 year'),
            'description' => $this->faker->paragraph(),
            'status' => $this->faker->numberBetween(0, 2),
            'total_time' => $this->faker->numberBetween(0, 1000),
            'progress_percentage' => $this->faker->numberBetween(0, 100),
        ];
    }
}