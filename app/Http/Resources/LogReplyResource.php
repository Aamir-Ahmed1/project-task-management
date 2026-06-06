<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LogReplyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'work_log_id' => $this->work_log_id,
            'user' => UserResource::make($this->whenLoaded('user')),
            'reply' => $this->reply,
            'created_at' => $this->created_at,
        ];
    }
}
