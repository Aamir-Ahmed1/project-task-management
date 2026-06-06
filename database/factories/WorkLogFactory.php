<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use App\Models\WorkLog;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkLogFactory extends Factory
{
    protected $model = WorkLog::class;

    public function definition(): array
    {
        return [
            'task_id' => Task::factory(),
            'user_id' => User::factory(),
            'description' => fake()->paragraph(),
            'hours_worked' => fake()->randomFloat(2, 0.5, 8),
            'logged_at' => fake()->dateTimeBetween('-1 week', 'now'),
        ];
    }
}
