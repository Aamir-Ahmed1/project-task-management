<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'name' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'priority' => fake()->randomElement(['low', 'medium', 'high', 'critical']),
            'status' => fake()->randomElement(['to_do', 'in_progress', 'in_review', 'completed', 'blocked']),
            'deadline' => fake()->dateTimeBetween('+1 day', '+2 months'),
            'estimated_hours' => fake()->randomFloat(2, 2, 80),
            'actual_hours' => fake()->randomFloat(2, 0, 60),
            'project_id' => Project::factory(),
            'assigned_to' => User::factory(),
            'created_by' => User::factory(),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attrs) => ['status' => 'completed']);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attrs) => [
            'deadline' => fake()->dateTimeBetween('-2 weeks', '-1 day'),
            'status' => fake()->randomElement(['to_do', 'in_progress', 'in_review', 'blocked']),
        ]);
    }

    public function dueSoon(): static
    {
        return $this->state(fn (array $attrs) => [
            'deadline' => fake()->dateTimeBetween('+1 hour', '+48 hours'),
        ]);
    }
}
