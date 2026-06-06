<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Task;
use App\Notifications\TaskAssigned;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TaskService
{
    public function __construct(
        protected Task $task,
        protected AuditLog $auditLog
    ) {}

    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = $this->task->query();

        if (! empty($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (! empty($filters['priority'])) {
            $query->byPriority($filters['priority']);
        }

        if (! empty($filters['assignee_id'])) {
            $query->byAssignee($filters['assignee_id']);
        }

        if (! empty($filters['project_id'])) {
            $query->byProject($filters['project_id']);
        }

        if (! empty($filters['project_ids'])) {
            $query->whereIn('project_id', (array) $filters['project_ids']);
        }

        if (! empty($filters['deadline_from']) && ! empty($filters['deadline_to'])) {
            $query->byDeadlineRange($filters['deadline_from'], $filters['deadline_to']);
        }

        if (! empty($filters['overdue'])) {
            $query->overdue();
        }

        if (! empty($filters['due_soon'])) {
            $query->dueSoon($filters['due_soon_hours'] ?? 48);
        }

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%'.$filters['search'].'%')
                    ->orWhere('description', 'like', '%'.$filters['search'].'%');
            });
        }

        $perPage = $filters['per_page'] ?? 15;

        return $query->with(['project:id,name', 'assignedUser:id,name'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function create(array $data): Task
    {
        $task = $this->task->create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'priority' => $data['priority'] ?? 'medium',
            'status' => $data['status'] ?? 'to_do',
            'deadline' => $data['deadline'] ?? null,
            'estimated_hours' => $data['estimated_hours'] ?? null,
            'project_id' => $data['project_id'],
            'assigned_to' => $data['assigned_to'] ?? null,
            'created_by' => $data['created_by'] ?? auth()->id() ?? 1,
        ]);

        if ($task->assigned_to) {
            $task->load('assignedUser', 'project');
            $task->assignedUser?->notify(new TaskAssigned($task, auth()->user()));
        }

        return $task;
    }

    public function update(Task $task, array $data): Task
    {
        $oldAssignee = $task->assigned_to;

        $task->update([
            'name' => $data['name'] ?? $task->name,
            'description' => $data['description'] ?? $task->description,
            'priority' => $data['priority'] ?? $task->priority,
            'status' => $data['status'] ?? $task->status,
            'deadline' => $data['deadline'] ?? $task->deadline,
            'estimated_hours' => $data['estimated_hours'] ?? $task->estimated_hours,
            'actual_hours' => $data['actual_hours'] ?? $task->actual_hours,
            'assigned_to' => $data['assigned_to'] ?? $task->assigned_to,
        ]);

        $fresh = $task->fresh();

        if (isset($data['assigned_to']) && (int) $data['assigned_to'] !== (int) $oldAssignee) {
            $fresh->load('assignedUser', 'project');
            $fresh->assignedUser?->notify(new TaskAssigned($fresh, auth()->user()));
        }

        return $fresh->load('assignedUser:id,name');
    }

    public function show(Task $task): Task
    {
        return $task->load([
            'project:id,name',
            'assignedUser:id,name',
            'creator:id,name',
            'workLogs' => function ($query) {
                $query->with('user:id,name')->latest();
            },
        ]);
    }

    public function getTimeline(Task $task): array
    {
        $logs = $this->auditLog->where('entity_type', Task::class)
            ->where('entity_id', $task->id)
            ->with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function (AuditLog $log) {
                return [
                    'id' => $log->id,
                    'action' => $log->action,
                    'user' => $log->user?->name,
                    'previous_values' => $log->previous_values,
                    'new_values' => $log->new_values,
                    'occurred_at' => $log->created_at,
                ];
            });

        return $logs->toArray();
    }
}
