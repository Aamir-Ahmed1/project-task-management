<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskOverdue extends Notification
{
    use Queueable;

    public function __construct(
        protected Task $task
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $daysOverdue = (int) now()->diffInDays($this->task->deadline, false);

        return (new MailMessage)
            ->subject("Overdue: Task \"{$this->task->name}\" is overdue")
            ->greeting("Hello {$notifiable->name},")
            ->line("The task \"{$this->task->name}\" is overdue by {$daysOverdue} days.")
            ->line("**Task:** {$this->task->name}")
            ->line("**Project:** {$this->task->project->name}")
            ->line("**Deadline was:** {$this->task->deadline->format('Y-m-d')}")
            ->line("**Priority:** {$this->task->priority}")
            ->action('View Task', url("/tasks/{$this->task->id}"))
            ->line('Please address this as soon as possible.');
    }

    public function toArray(object $notifiable): array
    {
        $daysOverdue = (int) now()->diffInDays($this->task->deadline, false);

        return [
            'title' => "Task Overdue: {$this->task->name}",
            'task_id' => $this->task->id,
            'task_name' => $this->task->name,
            'project_name' => $this->task->project->name,
            'deadline' => $this->task->deadline->format('Y-m-d'),
            'message' => "Task \"{$this->task->name}\" in project \"{$this->task->project->name}\" is overdue by {$daysOverdue} days.",
        ];
    }
}
