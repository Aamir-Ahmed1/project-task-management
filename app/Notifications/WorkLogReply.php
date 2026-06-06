<?php

namespace App\Notifications;

use App\Models\LogReply;
use App\Models\WorkLog;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class WorkLogReply extends Notification
{
    use Queueable;

    public function __construct(
        protected WorkLog $workLog,
        protected LogReply $reply
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Reply on your work log',
            'work_log_id' => $this->workLog->id,
            'task_id' => $this->workLog->task_id,
            'task_name' => $this->workLog->task->name ?? '',
            'reply_by' => $this->reply->user->name,
            'reply_text' => $this->reply->reply,
            'message' => "{$this->reply->user->name} replied to your work log on task \"{$this->workLog->task->name}\": \"{$this->reply->reply}\"",
        ];
    }
}
