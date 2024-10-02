<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use App\Models\Goal;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'goal_id' => Goal::factory(),
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'elapsed_time' => $this->faker->numberBetween(0, 1000),
            'estimated_time' => $this->faker->numberBetween(1, 1000),
            'start_date' => $this->faker->date(),
            'start_time' => $this->faker->time(),
            'priority' => $this->faker->numberBetween(1, 5),
            'order' => $this->faker->numberBetween(0, 100),
            'review_interval' => $this->faker->randomElement(['next_day', '7_days', '14_days', '28_days', '56_days', 'completed']),
            'repetition_count' => $this->faker->numberBetween(1, 10),
            'last_notification_sent' => $this->faker->optional()->dateTime(),
        ];
    }
}