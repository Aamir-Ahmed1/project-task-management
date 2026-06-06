<?php

namespace App\Console\Commands;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Notifications\DeadlineReminder;
use Illuminate\Console\Command;

class SendDeadlineReminders extends Command
{
    protected $signature = 'notifications:send-deadline-reminders';

    protected $description = 'Send deadline reminders to employees';

    public function handle(): void
    {
        $tasks = Task::with(['project', 'assignedUser'])
            ->where('deadline', '<=', now()->addHours(48))
            ->where('deadline', '>', now())
            ->where('status', '!=', TaskStatus::Completed->value)
            ->get();

        $grouped = [
            '48h' => [],
            '24h' => [],
            '12h' => [],
            '1h' => [],
        ];

        foreach ($tasks as $task) {
            $hoursUntilDeadline = (int) now()->diffInHours($task->deadline, false);

            if ($hoursUntilDeadline <= 1) {
                $grouped['1h'][] = $task;
            } elseif ($hoursUntilDeadline <= 12) {
                $grouped['12h'][] = $task;
            } elseif ($hoursUntilDeadline <= 24) {
                $grouped['24h'][] = $task;
            } else {
                $grouped['48h'][] = $task;
            }
        }

        foreach ($grouped as $type => $tasksByType) {
            if (empty($tasksByType)) {
                continue;
            }

            $this->info("Sending {$type} reminders for ".count($tasksByType).' task(s)...');

            foreach ($tasksByType as $task) {
                if (! $task->assignedUser) {
                    $this->warn("Task #{$task->id} has no assigned user, skipping.");

                    continue;
                }

                $task->assignedUser->notify(new DeadlineReminder($task, $type));
                $this->info("  Sent {$type} reminder to {$task->assignedUser->email} for task #{$task->id}");
            }
        }

        $this->info('Deadline reminders sent successfully.');
    }
}
