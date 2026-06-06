<?php

namespace App\Services;

use App\Models\LogReply;
use App\Models\WorkLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class WorkLogService
{
    public function __construct(
        protected WorkLog $workLog,
        protected LogReply $logReply
    ) {}

    public function create(array $data): WorkLog
    {
        return $this->workLog->create([
            'task_id' => $data['task_id'],
            'user_id' => $data['user_id'],
            'description' => $data['description'],
            'hours_worked' => $data['hours_worked'],
            'attachment' => $data['attachment'] ?? null,
            'logged_at' => $data['logged_at'] ?? now(),
        ]);
    }

    public function listByTask(int $taskId, array $filters = []): LengthAwarePaginator
    {
        $query = $this->workLog->byTask($taskId);

        if (! empty($filters['user_id'])) {
            $query->byUser($filters['user_id']);
        }

        if (! empty($filters['date_from']) && ! empty($filters['date_to'])) {
            $query->byDateRange($filters['date_from'], $filters['date_to']);
        }

        $perPage = $filters['per_page'] ?? 15;

        return $query->with('user:id,name')
            ->orderBy('logged_at', 'desc')
            ->paginate($perPage);
    }

    public function listByUser(int $userId, array $filters = []): LengthAwarePaginator
    {
        $query = $this->workLog->byUser($userId);

        if (! empty($filters['task_id'])) {
            $query->byTask($filters['task_id']);
        }

        if (! empty($filters['date_from']) && ! empty($filters['date_to'])) {
            $query->byDateRange($filters['date_from'], $filters['date_to']);
        }

        $perPage = $filters['per_page'] ?? 15;

        return $query->with('task:id,name')
            ->orderBy('logged_at', 'desc')
            ->paginate($perPage);
    }

    public function listByDateRange(string $start, string $end, array $filters = []): LengthAwarePaginator
    {
        $query = $this->workLog->byDateRange($start, $end);

        if (! empty($filters['user_id'])) {
            $query->byUser($filters['user_id']);
        }

        if (! empty($filters['task_id'])) {
            $query->byTask($filters['task_id']);
        }

        $perPage = $filters['per_page'] ?? 15;

        return $query->with(['user:id,name', 'task:id,name'])
            ->orderBy('logged_at', 'desc')
            ->paginate($perPage);
    }

    public function addReply(int $workLogId, int $userId, string $reply): LogReply
    {
        return $this->logReply->create([
            'work_log_id' => $workLogId,
            'user_id' => $userId,
            'reply' => $reply,
        ]);
    }

    public function getReplies(int $workLogId): Collection
    {
        return $this->logReply->where('work_log_id', $workLogId)
            ->with('user:id,name')
            ->orderBy('created_at', 'asc')
            ->get();
    }
}
