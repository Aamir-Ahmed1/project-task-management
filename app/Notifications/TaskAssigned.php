<?php

namespace App\Notifications;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskAssigned extends Notification
{
    use Queueable;

    public function __construct(
        protected Task $task,
        protected User $assignedBy
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => "Task Assigned: {$this->task->name}",
            'task_id' => $this->task->id,
            'task_name' => $this->task->name,
            'project_name' => $this->task->project->name ?? '',
            'assigned_by' => $this->assignedBy->name,
            'deadline' => $this->task->deadline?->format('Y-m-d'),
            'message' => "You have been assigned task \"{$this->task->name}\" in project \"{$this->task->project->name}\" by {$this->assignedBy->name}.",
        ];
    }
}
