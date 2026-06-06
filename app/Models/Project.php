<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use Auditable, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'status',
        'project_manager_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'project_manager_id' => 'integer',
            'created_by' => 'integer',
        ];
    }

    public function projectManager()
    {
        return $this->belongsTo(User::class, 'project_manager_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function getCompletionPercentageAttribute()
    {
        $total = $this->tasks_count ?? $this->tasks()->count();
        if ($total === 0) {
            return 0;
        }

        $completed = $this->completed_tasks_count ?? $this->tasks()->where('status', 'completed')->count();

        return round(($completed / $total) * 100, 2);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByManager($query, $userId)
    {
        return $query->where('project_manager_id', $userId);
    }

    public function scopeByDateRange($query, $start, $end)
    {
        return $query->whereBetween('created_at', [$start, $end]);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['planning', 'active']);
    }
}
