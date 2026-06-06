@extends('layouts.app')
@section('title', 'Project: ' . $project->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3"><i class="fas fa-folder me-2"></i>{{ $project->name }}</h1>
    <div class="d-flex gap-2">
        <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back
        </a>
        <a href="{{ route('projects.edit', $project) }}" class="btn btn-outline-primary">
            <i class="fas fa-edit me-1"></i>Edit
        </a>
        @if($project->status !== 'archived')
            <form action="{{ route('projects.destroy', $project) }}" method="POST"
                  onsubmit="return confirm('Archive this project?')" class="d-inline">
                @csrf @method('DELETE')
                <button class="btn btn-outline-secondary">
                    <i class="fas fa-archive me-1"></i>Archive
                </button>
            </form>
        @endif
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-8">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Project Details</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Description</strong>
                    <p class="mb-0 text-muted">{{ $project->description ?: 'No description provided.' }}</p>
                </div>
                <div class="row">
                    <div class="col-sm-6 mb-2">
                        <strong>Status</strong><br>
                        @php
                            $statusClasses = ['planning' => 'bg-secondary', 'active' => 'bg-warning', 'completed' => 'bg-success', 'archived' => 'bg-secondary'];
                        @endphp
                        <span class="badge {{ $statusClasses[$project->status] ?? 'bg-secondary' }}">
                            {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                        </span>
                    </div>
                    <div class="col-sm-6 mb-2">
                        <strong>Project Manager</strong><br>
                        <span class="text-muted">{{ $project->projectManager?->name ?? '—' }}</span>
                    </div>
                    <div class="col-sm-6 mb-2">
                        <strong>Start Date</strong><br>
                        <span class="text-muted">{{ $project->start_date ? $project->start_date->format('M d, Y') : '—' }}</span>
                    </div>
                    <div class="col-sm-6 mb-2">
                        <strong>End Date</strong><br>
                        <span class="text-muted">{{ $project->end_date ? $project->end_date->format('M d, Y') : '—' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Progress</h5>
            </div>
            <div class="card-body">
                @php $pct = $project->completionPercentage; @endphp
                <div class="text-center mb-3">
                    <span class="display-4 fw-bold {{ $pct === 100 ? 'text-success' : 'text-muted' }}">{{ $pct }}%</span>
                    <p class="text-muted mb-0">Complete</p>
                </div>
                <div class="progress mb-3" style="height: 12px;">
                    <div class="progress-bar {{ $pct === 100 ? 'bg-success' : ($pct > 50 ? 'bg-info' : 'bg-secondary') }}"
                         role="progressbar" style="width: {{ $pct }}%"></div>
                </div>
                <hr>
                <h6>Tasks Breakdown</h6>
                @php
                    $tasksCount = $project->tasks()->count();
                    $statusCount = $project->tasks()->selectRaw('status, count(*) as total')
                        ->groupBy('status')->pluck('total', 'status');
                @endphp
                <ul class="list-unstyled mb-0 small">
                    <li class="d-flex justify-content-between mb-1">
                        <span><i class="fas fa-circle text-secondary me-1"></i>To Do</span>
                        <span class="badge bg-secondary">{{ $statusCount['to_do'] ?? 0 }}</span>
                    </li>
                    <li class="d-flex justify-content-between mb-1">
                        <span><i class="fas fa-circle text-warning me-1"></i>In Progress</span>
                        <span class="badge bg-warning">{{ $statusCount['in_progress'] ?? 0 }}</span>
                    </li>
                    <li class="d-flex justify-content-between mb-1">
                        <span><i class="fas fa-circle text-info me-1"></i>In Review</span>
                        <span class="badge bg-info">{{ $statusCount['in_review'] ?? 0 }}</span>
                    </li>
                    <li class="d-flex justify-content-between mb-1">
                        <span><i class="fas fa-circle text-success me-1"></i>Completed</span>
                        <span class="badge bg-success">{{ $statusCount['completed'] ?? 0 }}</span>
                    </li>
                    <li class="d-flex justify-content-between">
                        <span><i class="fas fa-circle text-danger me-1"></i>Blocked</span>
                        <span class="badge bg-danger">{{ $statusCount['blocked'] ?? 0 }}</span>
                    </li>
                </ul>
                <hr>
                <p class="text-center text-muted mb-0 small">
                    <i class="fas fa-tasks me-1"></i>{{ $tasksCount }} total task{{ $tasksCount !== 1 ? 's' : '' }}
                </p>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fas fa-list-check me-2"></i>Tasks</h5>
    </div>
    <div class="card-body p-0">
        @php $tasks = $project->tasks()->with('assignedUser:id,name')->get(); @endphp
        @if($tasks->count())
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Task</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Assigned To</th>
                            <th>Deadline</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tasks as $task)
                            <tr>
                                <td>
                                    <a href="{{ route('tasks.show', $task) }}" class="text-decoration-none fw-semibold">
                                        {{ $task->name }}
                                    </a>
                                </td>
                                <td>
                                    @php
                                        $sClasses = ['to_do' => 'bg-secondary', 'in_progress' => 'bg-primary', 'in_review' => 'bg-info', 'completed' => 'bg-success', 'blocked' => 'bg-dark'];
                                    @endphp
                                    <span class="badge {{ $sClasses[$task->status] ?? 'bg-secondary' }}">
                                        {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $pClasses = ['low' => 'bg-success', 'medium' => 'bg-warning', 'high' => 'bg-danger', 'critical' => 'bg-dark'];
                                    @endphp
                                    <span class="badge {{ $pClasses[$task->priority] ?? 'bg-secondary' }}">
                                        {{ ucfirst($task->priority) }}
                                    </span>
                                </td>
                                <td>{{ $task->assignedUser?->name ?? '—' }}</td>
                                <td>
                                    @if($task->deadline)
                                        <span class="{{ $task->isOverdue() ? 'text-danger fw-semibold' : '' }}">
                                            {{ $task->deadline->format('M d, Y') }}
                                            @if($task->isOverdue())
                                                <span class="badge bg-danger ms-1">Overdue</span>
                                            @endif
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('tasks.show', $task) }}" class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-4">
                <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                <p class="text-muted mb-0">No tasks for this project yet.</p>
            </div>
        @endif
    </div>
</div>
@endsection
