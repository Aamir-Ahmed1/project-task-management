<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company().' Project',
            'description' => fake()->paragraph(),
            'start_date' => fake()->dateTimeBetween('-3 months', 'now'),
            'end_date' => fake()->dateTimeBetween('+1 month', '+6 months'),
            'status' => fake()->randomElement(['planning', 'active', 'active', 'completed']),
            'project_manager_id' => User::factory(),
            'created_by' => User::factory(),
        ];
    }

    public function planning(): static
    {
        return $this->state(fn (array $attrs) => ['status' => 'planning']);
    }

    public function active(): static
    {
        return $this->state(fn (array $attrs) => ['status' => 'active']);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attrs) => ['status' => 'completed']);
    }
}
