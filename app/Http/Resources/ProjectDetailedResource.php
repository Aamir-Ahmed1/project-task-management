<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectDetailedResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'status' => $this->status,
            'project_manager_id' => $this->project_manager_id,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'project_manager' => UserResource::make($this->whenLoaded('projectManager')),
            'tasks_count' => $this->whenCounted('tasks'),
            'completion_percentage' => $this->completion_percentage,
            'tasks' => TaskResource::collection($this->whenLoaded('tasks')),
        ];
    }
}
