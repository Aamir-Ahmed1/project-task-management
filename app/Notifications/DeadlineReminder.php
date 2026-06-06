<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DeadlineReminder extends Notification
{
    use Queueable;

    public function __construct(
        protected Task $task,
        protected string $type
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $hours = match ($this->type) {
            '48h' => 48,
            '24h' => 24,
            '12h' => 12,
            '1h' => 1,
            default => 0,
        };

        return (new MailMessage)
            ->subject("Reminder: Task \"{$this->task->name}\" is due in {$hours} hours")
            ->greeting("Hello {$notifiable->name},")
            ->line("This is a reminder that the task \"{$this->task->name}\" is due in {$hours} hours.")
            ->line("**Task:** {$this->task->name}")
            ->line("**Project:** {$this->task->project->name}")
            ->line("**Deadline:** {$this->task->deadline->format('Y-m-d')}")
            ->line("**Priority:** {$this->task->priority}")
            ->action('View Task', url("/tasks/{$this->task->id}"))
            ->line('Please ensure the task is completed on time.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'task_id' => $this->task->id,
            'task_name' => $this->task->name,
            'project_name' => $this->task->project->name,
            'deadline' => $this->task->deadline->format('Y-m-d'),
            'type' => $this->type,
            'message' => "Reminder: Task {$this->task->name} is due in {$this->type}",
        ];
    }
}
