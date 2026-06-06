<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskDetailedResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'priority' => $this->priority,
            'status' => $this->status,
            'deadline' => $this->deadline,
            'estimated_hours' => $this->estimated_hours,
            'actual_hours' => $this->actual_hours,
            'project_id' => $this->project_id,
            'assigned_to' => $this->assigned_to,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'project' => $this->when($this->relationLoaded('project'), function () {
                return [
                    'id' => $this->project->id,
                    'name' => $this->project->name,
                ];
            }),
            'assigned_user' => UserResource::make($this->whenLoaded('assignedUser')),
            'creator' => UserResource::make($this->whenLoaded('creator')),
            'is_overdue' => $this->isOverdue(),
            'work_logs_count' => $this->whenCounted('workLogs'),
            'work_logs' => WorkLogResource::collection($this->whenLoaded('workLogs')),
        ];
    }
}
