<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\WorkLog;

class DashboardService
{
    public function __construct(
        protected Project $project,
        protected Task $task,
        protected User $user,
        protected WorkLog $workLog
    ) {}

    public function adminDashboard(): array
    {
        $totalProjects = $this->project->withTrashed()->count();
        $activeProjects = $this->project->active()->count();
        $archivedProjects = $this->project->onlyTrashed()->count();

        $totalTasks = $this->task->withTrashed()->count();
        $completedTasks = $this->task->where('status', 'completed')->count();
        $overdueTasks = $this->task->overdue()->count();

        $activeEmployees = $this->user->active()->role('employee')->count();
        $projectManagers = $this->user->role('project-manager')->count();

        $totalHoursLogged = $this->workLog->sum('hours_worked');

        $tasksByStatus = $this->task->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $recentProjects = $this->project->withCount('tasks')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($project) {
                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'status' => $project->status,
                    'tasks_count' => $project->tasks_count,
                    'completion_percentage' => $project->completion_percentage,
                ];
            });

        return [
            'total_projects' => $totalProjects,
            'active_projects' => $activeProjects,
            'archived_projects' => $archivedProjects,
            'total_tasks' => $totalTasks,
            'completed_tasks' => $completedTasks,
            'overdue_tasks' => $overdueTasks,
            'active_employees' => $activeEmployees,
            'project_managers' => $projectManagers,
            'total_hours_logged' => (float) $totalHoursLogged,
            'tasks_by_status' => $tasksByStatus->toArray(),
            'recent_projects' => $recentProjects->toArray(),
        ];
    }

    public function projectManagerDashboard(int $userId): array
    {
        $managedProjects = $this->project->byManager($userId)->withCount('tasks')->get();
        $managedProjectIds = $managedProjects->pluck('id');

        $activeTasks = $this->task->whereIn('project_id', $managedProjectIds)
            ->whereNotIn('status', ['completed'])
            ->count();

        $upcomingDeadlines = $this->task->whereIn('project_id', $managedProjectIds)
            ->dueSoon(48)
            ->with(['assignedUser:id,name', 'project:id,name'])
            ->get()
            ->map(fn ($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'deadline' => $t->deadline->format('Y-m-d'),
                'priority' => $t->priority,
                'status' => $t->status,
                'assigned_user' => $t->assignedUser?->name,
                'project_name' => $t->project?->name,
            ]);

        $overdueTasks = $this->task->whereIn('project_id', $managedProjectIds)
            ->overdue()
            ->with(['assignedUser:id,name', 'project:id,name'])
            ->get()
            ->map(fn ($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'deadline' => $t->deadline->format('Y-m-d'),
                'priority' => $t->priority,
                'status' => $t->status,
                'assigned_user' => $t->assignedUser?->name,
                'project_name' => $t->project?->name,
            ]);

        $completedTasks = $this->task->whereIn('project_id', $managedProjectIds)
            ->where('status', 'completed')
            ->count();

        $employeeProductivity = User::whereHas('assignedTasks', function ($q) use ($managedProjectIds) {
            $q->whereIn('project_id', $managedProjectIds);
        })->withCount([
            'assignedTasks' => function ($q) use ($managedProjectIds) {
                $q->whereIn('project_id', $managedProjectIds);
            },
            'assignedTasks as completed_tasks_count' => function ($q) use ($managedProjectIds) {
                $q->whereIn('project_id', $managedProjectIds)->where('status', 'completed');
            },
        ])->get()->map(function ($employee) {
            return [
                'id' => $employee->id,
                'name' => $employee->name,
                'assigned_tasks' => $employee->assigned_tasks_count,
                'completed_tasks' => $employee->completed_tasks_count,
            ];
        });

        $projectSummaries = $managedProjects->map(function ($project) {
            return [
                'id' => $project->id,
                'name' => $project->name,
                'status' => $project->status,
                'tasks_count' => $project->tasks_count,
                'completion_percentage' => $project->completion_percentage,
            ];
        });

        return [
            'managed_projects' => $projectSummaries->toArray(),
            'total_managed_projects' => $managedProjects->count(),
            'active_tasks' => $activeTasks,
            'completed_tasks' => $completedTasks,
            'overdue_tasks_count' => $overdueTasks->count(),
            'overdue_tasks' => $overdueTasks->toArray(),
            'upcoming_deadlines' => $upcomingDeadlines->toArray(),
            'employee_productivity' => $employeeProductivity->toArray(),
        ];
    }

    public function employeeDashboard(int $userId): array
    {
        $assignedTasks = $this->task->byAssignee($userId)->count();
        $completedTasks = $this->task->byAssignee($userId)->where('status', 'completed')->count();
        $pendingTasks = $assignedTasks - $completedTasks;

        $tasksDueSoon = $this->task->byAssignee($userId)
            ->dueSoon(48)
            ->with('project:id,name')
            ->get();

        $overdueTasks = $this->task->byAssignee($userId)
            ->overdue()
            ->with('project:id,name')
            ->get();

        $recentActivity = $this->workLog->byUser($userId)
            ->with('task:id,name')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'task_name' => $log->task?->name,
                    'description' => $log->description,
                    'hours_worked' => $log->hours_worked,
                    'logged_at' => $log->logged_at,
                ];
            });

        $tasksByStatus = $this->task->byAssignee($userId)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $totalHoursLogged = $this->workLog->byUser($userId)->sum('hours_worked');

        return [
            'total_assigned_tasks' => $assignedTasks,
            'completed_tasks' => $completedTasks,
            'pending_tasks' => $pendingTasks,
            'overdue_tasks' => $overdueTasks->count(),
            'tasks_due_soon' => $tasksDueSoon->toArray(),
            'tasks_by_status' => $tasksByStatus->toArray(),
            'total_hours_logged' => (float) $totalHoursLogged,
            'recent_activity' => $recentActivity->toArray(),
        ];
    }
}
