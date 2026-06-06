<?php

namespace App\Services;

use App\Models\Project;
use App\Notifications\ProjectAssigned;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class ProjectService
{
    public function __construct(
        protected Project $project
    ) {}

    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = $this->project->query();

        if (! empty($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (! empty($filters['manager_id'])) {
            $query->byManager($filters['manager_id']);
        }

        if (! empty($filters['date_from']) && ! empty($filters['date_to'])) {
            $query->byDateRange($filters['date_from'], $filters['date_to']);
        }

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%'.$filters['search'].'%')
                    ->orWhere('description', 'like', '%'.$filters['search'].'%');
            });
        }

        $perPage = $filters['per_page'] ?? 15;

        return $query->with('projectManager:id,name')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function create(array $data): Project
    {
        $project = $this->project->create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'start_date' => $data['start_date'] ?? now()->format('Y-m-d'),
            'end_date' => $data['end_date'] ?? now()->addMonth()->format('Y-m-d'),
            'status' => $data['status'] ?? 'planning',
            'project_manager_id' => $data['project_manager_id'] ?? auth()->id(),
            'created_by' => $data['created_by'] ?? auth()->id() ?? 1,
        ]);

        if ($project->project_manager_id && $project->project_manager_id !== auth()->id()) {
            $project->load('projectManager');
            $project->projectManager?->notify(new ProjectAssigned($project, auth()->user()));
        }

        Cache::forget('dashboard.admin');
        Cache::forget('report.projects.all');

        return $project;
    }

    public function update(Project $project, array $data): Project
    {
        $oldManagerId = $project->project_manager_id;

        $project->update([
            'name' => $data['name'] ?? $project->name,
            'description' => $data['description'] ?? $project->description,
            'start_date' => $data['start_date'] ?? $project->start_date,
            'end_date' => $data['end_date'] ?? $project->end_date,
            'status' => $data['status'] ?? $project->status,
            'project_manager_id' => $data['project_manager_id'] ?? $project->project_manager_id,
        ]);

        $fresh = $project->fresh();

        if (isset($data['project_manager_id']) && (int) $data['project_manager_id'] !== (int) $oldManagerId) {
            $fresh->load('projectManager');
            $fresh->projectManager?->notify(new ProjectAssigned($fresh, auth()->user()));
        }

        Cache::forget('dashboard.admin');
        Cache::forget("report.project.{$project->id}");
        Cache::forget('report.projects.all');

        return $fresh;
    }

    public function archive(Project $project): void
    {
        $project->update(['status' => 'archived']);
        $project->delete();
    }

    public function show(Project $project): Project
    {
        $project->loadCount('tasks');

        return $project;
    }

    public function getProjectProgress(Project $project): array
    {
        $tasks = $project->tasks()->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $total = $tasks->sum();
        $completed = $tasks->get('completed', 0);
        $completionPercentage = $total > 0 ? round(($completed / $total) * 100, 2) : 0;

        return [
            'project_id' => $project->id,
            'project_name' => $project->name,
            'total_tasks' => $total,
            'completion_percentage' => $completionPercentage,
            'tasks_by_status' => $tasks->toArray(),
        ];
    }
}
