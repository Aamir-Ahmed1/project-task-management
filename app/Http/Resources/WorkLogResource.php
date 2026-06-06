<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'task_id' => $this->task_id,
            'user' => UserResource::make($this->whenLoaded('user')),
            'description' => $this->description,
            'hours_worked' => $this->hours_worked,
            'attachment_url' => $this->attachment ? url('storage/'.$this->attachment) : null,
            'logged_at' => $this->logged_at,
            'replies_count' => $this->whenCounted('replies'),
            'replies' => LogReplyResource::collection($this->whenLoaded('replies')),
        ];
    }
}
