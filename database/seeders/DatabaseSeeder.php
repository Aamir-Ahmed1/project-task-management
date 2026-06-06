<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\WorkLog;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        $manager = User::where('email', 'manager@example.com')->first();
        $employee = User::where('email', 'employee@example.com')->first();
        $admin = User::where('email', 'admin@example.com')->first();

        // Project 1
        $project1 = Project::create([
            'name' => 'Website Redesign',
            'description' => 'Complete redesign of the company website with modern UI/UX.',
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonths(2),
            'status' => 'active',
            'project_manager_id' => $manager->id,
            'created_by' => $admin->id,
        ]);

        // Project 2
        $project2 = Project::create([
            'name' => 'Mobile App Development',
            'description' => 'Native mobile application for iOS and Android.',
            'start_date' => now()->subWeeks(2),
            'end_date' => now()->addMonths(4),
            'status' => 'active',
            'project_manager_id' => $manager->id,
            'created_by' => $admin->id,
        ]);

        // Tasks for Project 1
        $task1 = Task::create([
            'name' => 'Design homepage mockup',
            'description' => 'Create wireframes and high-fidelity mockups for the homepage.',
            'priority' => 'high',
            'status' => 'completed',
            'deadline' => now()->addDays(5),
            'estimated_hours' => 24,
            'actual_hours' => 20,
            'project_id' => $project1->id,
            'assigned_to' => $employee->id,
            'created_by' => $manager->id,
        ]);

        $task2 = Task::create([
            'name' => 'Implement responsive navigation',
            'description' => 'Build a responsive navigation bar with mobile hamburger menu.',
            'priority' => 'medium',
            'status' => 'in_progress',
            'deadline' => now()->addDays(10),
            'estimated_hours' => 16,
            'actual_hours' => 6,
            'project_id' => $project1->id,
            'assigned_to' => $employee->id,
            'created_by' => $manager->id,
        ]);

        // Tasks for Project 2
        $task3 = Task::create([
            'name' => 'Set up CI/CD pipeline',
            'description' => 'Configure GitHub Actions for automated testing and deployment.',
            'priority' => 'critical',
            'status' => 'to_do',
            'deadline' => now()->addDays(3),
            'estimated_hours' => 12,
            'actual_hours' => 0,
            'project_id' => $project2->id,
            'assigned_to' => $employee->id,
            'created_by' => $manager->id,
        ]);

        $task4 = Task::create([
            'name' => 'User authentication module',
            'description' => 'Implement login, register, and password reset screens.',
            'priority' => 'high',
            'status' => 'in_review',
            'deadline' => now()->addWeek(),
            'estimated_hours' => 20,
            'actual_hours' => 18,
            'project_id' => $project2->id,
            'assigned_to' => $employee->id,
            'created_by' => $manager->id,
        ]);

        // Work Logs
        WorkLog::create([
            'task_id' => $task1->id,
            'user_id' => $employee->id,
            'description' => 'Worked on homepage hero section and typography.',
            'hours_worked' => 6,
            'logged_at' => now()->subDays(3),
        ]);

        WorkLog::create([
            'task_id' => $task2->id,
            'user_id' => $employee->id,
            'description' => 'Implemented navbar dropdown menus and mobile toggle.',
            'hours_worked' => 4,
            'logged_at' => now()->subDay(),
        ]);

        WorkLog::create([
            'task_id' => $task4->id,
            'user_id' => $employee->id,
            'description' => 'Reviewed and refactored auth controller logic.',
            'hours_worked' => 3.5,
            'logged_at' => now()->subHours(5),
        ]);
    }
}
