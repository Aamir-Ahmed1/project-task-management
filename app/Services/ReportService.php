<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Task;
use App\Models\WorkLog;

class ReportService
{
    public function __construct(
        protected Project $project,
        protected Task $task,
        protected WorkLog $workLog
    ) {}

    public function getProjectReport(int $projectId): array
    {
        $project = $this->project->withCount('tasks')->findOrFail($projectId);

        $taskStats = $this->task->byProject($projectId)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending
            ")
            ->first();

        $tasksByStatus = $this->task->byProject($projectId)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $totalHoursLogged = $this->workLog->whereHas('task', function ($q) use ($projectId) {
            $q->byProject($projectId);
        })->sum('hours_worked');

        return [
            'project_id' => $project->id,
            'project_name' => $project->name,
            'project_status' => $project->status,
            'completion_percentage' => $project->completion_percentage,
            'total_tasks' => (int) $taskStats->total,
            'completed_tasks' => (int) $taskStats->completed,
            'pending_tasks' => (int) $taskStats->pending,
            'tasks_by_status' => $tasksByStatus->toArray(),
            'total_hours_logged' => (float) $totalHoursLogged,
        ];
    }

    public function getEmployeeReport(int $userId): array
    {
        $totalAssigned = $this->task->byAssignee($userId)->count();
        $completedTasks = $this->task->byAssignee($userId)
            ->where('status', 'completed')
            ->get();

        $completedCount = $completedTasks->count();

        $avgCompletionTime = null;
        if ($completedCount > 0) {
            $avgCompletionTime = $completedTasks->avg(function ($task) {
                return $task->created_at->diffInHours($task->updated_at);
            });
        }

        $totalHoursLogged = $this->workLog->byUser($userId)->sum('hours_worked');

        $tasksByStatus = $this->task->byAssignee($userId)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $overdueTasks = $this->task->byAssignee($userId)->overdue()->count();

        return [
            'user_id' => $userId,
            'total_assigned_tasks' => $totalAssigned,
            'completed_tasks' => $completedCount,
            'pending_tasks' => $totalAssigned - $completedCount,
            'overdue_tasks' => $overdueTasks,
            'tasks_by_status' => $tasksByStatus->toArray(),
            'average_completion_time_hours' => $avgCompletionTime ? round($avgCompletionTime, 2) : null,
            'total_hours_logged' => (float) $totalHoursLogged,
        ];
    }

    public function getAllProjectsReport(): array
    {
        $projects = $this->project->withCount('tasks')->get();

        $totalProjects = $projects->count();
        $activeProjects = $projects->whereIn('status', ['planning', 'active'])->count();
        $archivedProjects = $projects->where('status', 'archived')->count();

        $totalTasks = $this->task->count();
        $totalCompletedTasks = $this->task->where('status', 'completed')->count();
        $totalOverdueTasks = $this->task->overdue()->count();
        $totalHoursLogged = $this->workLog->sum('hours_worked');

        $avgCompletionPercentage = $projects->avg(function ($project) {
            return $project->completion_percentage;
        });

        $projectSummaries = $projects->map(function ($project) {
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
            'total_completed_tasks' => $totalCompletedTasks,
            'total_overdue_tasks' => $totalOverdueTasks,
            'total_hours_logged' => (float) $totalHoursLogged,
            'average_completion_percentage' => round($avgCompletionPercentage, 2),
            'projects' => $projectSummaries->toArray(),
        ];
    }
}
