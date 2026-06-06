<?php

namespace App\Notifications;

use App\Models\Project;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProjectAssigned extends Notification
{
    use Queueable;

    public function __construct(
        protected Project $project,
        protected User $assignedBy
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => "Project Assigned: {$this->project->name}",
            'project_id' => $this->project->id,
            'project_name' => $this->project->name,
            'assigned_by' => $this->assignedBy->name,
            'message' => "You have been assigned as project manager for \"{$this->project->name}\" by {$this->assignedBy->name}.",
        ];
    }
}
