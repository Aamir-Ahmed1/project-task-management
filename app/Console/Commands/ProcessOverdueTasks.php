<?php

namespace App\Console\Commands;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Notifications\TaskOverdue;
use Illuminate\Console\Command;

class ProcessOverdueTasks extends Command
{
    protected $signature = 'notifications:process-overdue';

    protected $description = 'Send overdue alerts for past-deadline tasks';

    public function handle(): void
    {
        $tasks = Task::with(['project', 'assignedUser', 'project.projectManager'])
            ->where('deadline', '<', now())
            ->where('status', '!=', TaskStatus::Completed->value)
            ->get();

        if ($tasks->isEmpty()) {
            $this->info('No overdue tasks found.');

            return;
        }

        $this->info('Processing '.$tasks->count().' overdue task(s)...');

        foreach ($tasks as $task) {
            $notified = [];

            if ($task->assignedUser) {
                $task->assignedUser->notify(new TaskOverdue($task));
                $notified[] = $task->assignedUser->email;
                $this->info("  Notified employee {$task->assignedUser->email} for task #{$task->id}");
            }

            if ($task->project && $task->project->projectManager) {
                $pm = $task->project->projectManager;
                if (! in_array($pm->email, $notified)) {
                    $pm->notify(new TaskOverdue($task));
                    $this->info("  Notified PM {$pm->email} for task #{$task->id}");
                }
            }

            if (empty($notified)) {
                $this->warn("  Task #{$task->id} has no assigned user or project manager, skipping.");
            }
        }

        $this->info('Overdue notifications sent successfully.');
    }
}
