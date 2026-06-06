@extends('layouts.app')
@section('title', 'Task: ' . $task->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3"><i class="fas fa-tasks me-2"></i>{{ $task->name }}</h1>
    <div class="d-flex gap-2">
        <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
        <a href="{{ route('tasks.edit', $task) }}" class="btn btn-outline-primary">
            <i class="fas fa-edit me-1"></i>Edit
        </a>
        <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="d-inline"
              onsubmit="return confirm('Delete this task?')">
            @csrf @method('DELETE')
            <button class="btn btn-outline-danger">
                <i class="fas fa-trash me-1"></i>Delete
            </button>
        </form>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-8">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Task Details</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Description</strong>
                    <p class="mb-0 text-muted">{{ $task->description ?: 'No description provided.' }}</p>
                </div>
                <div class="row">
                    <div class="col-sm-6 mb-2">
                        <strong>Priority</strong><br>
                        @php
                            $pClasses = ['low' => 'bg-success', 'medium' => 'bg-warning', 'high' => 'bg-danger', 'critical' => 'bg-dark'];
                        @endphp
                        <span class="badge {{ $pClasses[$task->priority] ?? 'bg-secondary' }}">
                            {{ ucfirst($task->priority) }}
                        </span>
                    </div>
                    <div class="col-sm-6 mb-2">
                        <strong>Status</strong><br>
                        @php
                            $sClasses = ['to_do' => 'bg-secondary', 'in_progress' => 'bg-warning', 'review' => 'bg-info', 'completed' => 'bg-success', 'blocked' => 'bg-danger'];
                        @endphp
                        <span class="badge {{ $sClasses[$task->status] ?? 'bg-secondary' }}">
                            {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                        </span>
                    </div>
                    <div class="col-sm-6 mb-2">
                        <strong>Project</strong><br>
                        @if($task->project)
                            <a href="{{ route('projects.show', $task->project) }}" class="text-decoration-none">
                                {{ $task->project->name }}
                            </a>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </div>
                    <div class="col-sm-6 mb-2">
                        <strong>Assigned To</strong><br>
                        <span class="text-muted">{{ $task->assignedUser?->name ?? '—' }}</span>
                    </div>
                    <div class="col-sm-6 mb-2">
                        <strong>Deadline</strong><br>
                        @if($task->deadline)
                            <span class="{{ $task->isOverdue() ? 'text-danger fw-semibold' : 'text-muted' }}">
                                {{ $task->deadline->format('M d, Y') }}
                                @if($task->isOverdue())
                                    <span class="badge bg-danger ms-1">Overdue</span>
                                @endif
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </div>
                    <div class="col-sm-6 mb-2">
                        <strong>Estimated Hours</strong><br>
                        <span class="text-muted">{{ $task->estimated_hours ? number_format($task->estimated_hours, 1) : '—' }}</span>
                    </div>
                    <div class="col-sm-6 mb-2">
                        <strong>Actual Hours</strong><br>
                        <span class="text-muted">{{ $task->actual_hours ? number_format($task->actual_hours, 1) : '—' }}</span>
                    </div>
                    <div class="col-sm-6 mb-2">
                        <strong>Created By</strong><br>
                        <span class="text-muted">{{ $task->creator?->name ?? '—' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-sync-alt me-2"></i>Update Status</h5>
            </div>
            <div class="card-body">
                @can('update', $task)
                    <form action="{{ route('tasks.updateStatus', $task) }}" method="POST">
                        @csrf
                        <div class="input-group">
                            <select name="status" class="form-select">
                                <option value="to_do" {{ $task->status === 'to_do' ? 'selected' : '' }}>To Do</option>
                                <option value="in_progress" {{ $task->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="review" {{ $task->status === 'review' ? 'selected' : '' }}>In Review</option>
                                <option value="completed" {{ $task->status === 'completed' ? 'selected' : '' }}>Completed</option>
                            </select>
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </form>
                @else
                    <p class="text-muted mb-0 small">You do not have permission to update the status.</p>
                @endcan
            </div>
        </div>

        <div class="card shadow-sm mt-3">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Summary</h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0 small">
                    <li class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Logged Hours</span>
                        <strong>{{ $task->workLogs->sum('hours_worked') ? number_format($task->workLogs->sum('hours_worked'), 1) : '0.0' }}</strong>
                    </li>
                    <li class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Log Entries</span>
                        <strong>{{ $task->workLogs->count() }}</strong>
                    </li>
                    <li class="d-flex justify-content-between">
                        <span class="text-muted">Created</span>
                        <strong>{{ $task->created_at->format('M d, Y') }}</strong>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<ul class="nav nav-tabs mb-3" id="taskTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="work-logs-tab" data-bs-toggle="tab"
                data-bs-target="#work-logs" type="button" role="tab">
            <i class="fas fa-hourglass-half me-1"></i>Work Logs
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="timeline-tab" data-bs-toggle="tab"
                data-bs-target="#timeline" type="button" role="tab">
            <i class="fas fa-history me-1"></i>Timeline
        </button>
    </li>
</ul>

<div class="tab-content" id="taskTabsContent">
    <div class="tab-pane fade show active" id="work-logs" role="tabpanel">
        <div class="card shadow-sm">
            <div class="card-body">
                @if($task->workLogs->count())
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>User</th>
                                    <th>Description</th>
                                    <th>Hours</th>
                                    <th>Logged At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($task->workLogs as $log)
                                    <tr>
                                        <td>{{ $log->user?->name ?? '—' }}</td>
                                        <td>{{ $log->description ?: '—' }}</td>
                                        <td>{{ $log->hours_worked ? number_format($log->hours_worked, 1) : '—' }}</td>
                                        <td>{{ $log->logged_at?->format('M d, Y H:i') ?? $log->created_at->format('M d, Y H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-hourglass-start fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">No work logs yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="timeline" role="tabpanel">
        <div class="card shadow-sm">
            <div class="card-body">
                @php
                    $timeline = app(\App\Services\TaskService::class)->getTimeline($task);
                @endphp
                @if(count($timeline))
                    <ul class="list-unstyled mb-0">
                        @foreach($timeline as $entry)
                            <li class="d-flex align-items-start mb-3 pb-2 border-bottom">
                                <div class="me-3 text-center" style="min-width: 40px;">
                                    <i class="fas fa-circle text-primary" style="font-size: 10px;"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between">
                                        <strong>{{ ucfirst($entry['action']) }}</strong>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($entry['occurred_at'])->diffForHumans() }}</small>
                                    </div>
                                    <small class="text-muted">
                                        by {{ $entry['user'] ?? 'System' }}
                                        on {{ \Carbon\Carbon::parse($entry['occurred_at'])->format('M d, Y H:i') }}
                                    </small>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-history fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">No activity recorded yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
