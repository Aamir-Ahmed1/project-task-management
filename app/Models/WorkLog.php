<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class WorkLog extends Model
{
    use Auditable;

    protected $fillable = [
        'task_id',
        'user_id',
        'description',
        'hours_worked',
        'attachment',
        'logged_at',
    ];

    protected function casts(): array
    {
        return [
            'hours_worked' => 'decimal:2',
            'logged_at' => 'datetime',
        ];
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function replies()
    {
        return $this->hasMany(LogReply::class);
    }

    public function scopeByTask($query, $taskId)
    {
        return $query->where('task_id', $taskId);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByDateRange($query, $start, $end)
    {
        return $query->whereBetween('logged_at', [$start, $end]);
    }
}
