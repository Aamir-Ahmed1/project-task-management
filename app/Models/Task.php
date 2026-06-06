<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use Auditable, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'priority',
        'status',
        'deadline',
        'estimated_hours',
        'actual_hours',
        'project_id',
        'assigned_to',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'deadline' => 'date',
            'estimated_hours' => 'decimal:2',
            'actual_hours' => 'decimal:2',
            'project_id' => 'integer',
            'assigned_to' => 'integer',
            'created_by' => 'integer',
        ];
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function workLogs()
    {
        return $this->hasMany(WorkLog::class);
    }

    public function isOverdue()
    {
        return $this->deadline->isPast() && $this->status !== 'completed';
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeByProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeByAssignee($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeByDeadlineRange($query, $start, $end)
    {
        return $query->whereBetween('deadline', [$start, $end]);
    }

    public function scopeOverdue($query)
    {
        return $query->where('deadline', '<', now())
            ->whereNotIn('status', ['completed']);
    }

    public function scopeDueSoon($query, $hours = 48)
    {
        return $query->whereBetween('deadline', [now(), now()->addHours($hours)])
            ->whereNotIn('status', ['completed']);
    }
}
