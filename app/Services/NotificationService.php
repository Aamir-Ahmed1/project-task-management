<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\DeadlineReminder;
use App\Notifications\TaskOverdue;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Notifications\DatabaseNotification;

class NotificationService
{
    public function __construct(
        protected User $user
    ) {}

    public function getUserNotifications(User $user, array $filters = []): LengthAwarePaginator
    {
        $query = $user->notifications();

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (! empty($filters['unread_only'])) {
            $query->whereNull('read_at');
        }

        $perPage = $filters['per_page'] ?? 15;

        return $query->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function markAsRead(string $notificationId): DatabaseNotification
    {
        $notification = DatabaseNotification::findOrFail($notificationId);
        $notification->markAsRead();

        return $notification->fresh();
    }

    public function markAllAsRead(User $user): void
    {
        $user->unreadNotifications->markAsRead();
    }

    public function sendDeadlineReminder(User $user, array $tasks, string $type): void
    {
        foreach ($tasks as $task) {
            $user->notify(new DeadlineReminder($task, $type));
        }
    }

    public function sendOverdueAlert(User $user, array $tasks): void
    {
        foreach ($tasks as $task) {
            $user->notify(new TaskOverdue($task));
        }
    }
}
