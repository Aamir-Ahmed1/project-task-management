@extends('layouts.app')
@section('title', 'Tasks')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3"><i class="fas fa-list-check me-2"></i>Tasks</h1>
    <a href="{{ route('tasks.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>Create Task
    </a>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('tasks.index') }}" class="row g-2 align-items-end">
            <div class="col-auto">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="to_do" {{ request('status') === 'to_do' ? 'selected' : '' }}>To Do</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="in_review" {{ request('status') === 'in_review' ? 'selected' : '' }}>In Review</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="blocked" {{ request('status') === 'blocked' ? 'selected' : '' }}>Blocked</option>
                </select>
            </div>
            <div class="col-auto">
                <select name="priority" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Priorities</option>
                    <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                    <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                    <option value="critical" {{ request('priority') === 'critical' ? 'selected' : '' }}>Critical</option>
                </select>
            </div>
            <div class="col-auto">
                <select name="project_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Projects</option>
                    @foreach(App\Models\Project::all(['id', 'name']) as $p)
                        <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <select name="assignee_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Assignees</option>
                    @foreach(App\Models\User::all(['id', 'name']) as $u)
                        <option value="{{ $u->id }}" {{ request('assignee_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <input type="date" name="deadline_from" class="form-control form-control-sm" placeholder="From"
                       value="{{ request('deadline_from') }}">
            </div>
            <div class="col-auto">
                <input type="date" name="deadline_to" class="form-control form-control-sm" placeholder="To"
                       value="{{ request('deadline_to') }}">
            </div>
            <div class="col">
                <div class="input-group input-group-sm">
                    <input type="text" name="search" class="form-control" placeholder="Search tasks..."
                           value="{{ request('search') }}">
                    <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                    @if(count(request()->all()) > 0)
                        <a href="{{ route('tasks.index') }}" class="btn btn-outline-danger"><i class="fas fa-times"></i></a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

@if($tasks->count())
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Project</th>
                    <th>Priority</th>
                    <th>Status</th>
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
                            @if($task->project)
                                <a href="{{ route('projects.show', $task->project) }}" class="text-decoration-none">
                                    {{ $task->project->name }}
                                </a>
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            @php
                                $pClasses = ['low' => 'bg-success', 'medium' => 'bg-warning', 'high' => 'bg-danger', 'critical' => 'bg-dark'];
                            @endphp
                            <span class="badge {{ $pClasses[$task->priority] ?? 'bg-secondary' }}">
                                {{ ucfirst($task->priority) }}
                            </span>
                        </td>
                        <td>
                            @php
                                $sClasses = ['to_do' => 'bg-secondary', 'in_progress' => 'bg-primary', 'in_review' => 'bg-info', 'completed' => 'bg-success', 'blocked' => 'bg-dark'];
                            @endphp
                            <span class="badge {{ $sClasses[$task->status] ?? 'bg-secondary' }}">
                                {{ ucfirst(str_replace('_', ' ', $task->status)) }}
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
                            <a href="{{ route('tasks.show', $task) }}" class="btn btn-sm btn-outline-info" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('tasks.edit', $task) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Delete this task?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-center">
        {{ $tasks->withQueryString()->links() }}
    </div>
@else
    <div class="text-center py-5">
        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
        <h5 class="text-muted">No tasks found</h5>
        <p class="text-muted">Create a task to get started.</p>
        <a href="{{ route('tasks.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Create Task
        </a>
    </div>
@endif
@endsection
