@extends('layouts.app')
@section('title', 'Projects')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3"><i class="fas fa-folder me-2"></i>Projects</h1>
    @can('create', App\Models\Project::class)
        <a href="{{ route('projects.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Create Project
        </a>
    @endcan
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('projects.index') }}" class="row g-2 align-items-end">
            <div class="col-auto">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="planning" {{ request('status') === 'planning' ? 'selected' : '' }}>Planning</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Archived</option>
                </select>
            </div>
            <div class="col">
                <div class="input-group input-group-sm">
                    <input type="text" name="search" class="form-control" placeholder="Search projects..." value="{{ request('search') }}">
                    <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                    @if(request('search'))
                        <a href="{{ route('projects.index') }}" class="btn btn-outline-danger"><i class="fas fa-times"></i></a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

@if($projects->count())
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Project Manager</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th class="text-center">Tasks</th>
                    <th class="text-center">Completion</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($projects as $project)
                    <tr>
                        <td>
                            <a href="{{ route('projects.show', $project) }}" class="text-decoration-none fw-semibold">
                                {{ $project->name }}
                            </a>
                        </td>
                        <td>
                            @php
                                $statusClasses = ['planning' => 'bg-secondary', 'active' => 'bg-warning', 'completed' => 'bg-success', 'archived' => 'bg-secondary'];
                            @endphp
                            <span class="badge {{ $statusClasses[$project->status] ?? 'bg-secondary' }}">
                                {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                            </span>
                        </td>
                        <td>{{ $project->projectManager?->name ?? '—' }}</td>
                        <td>{{ $project->start_date ? $project->start_date->format('M d, Y') : '—' }}</td>
                        <td>{{ $project->end_date ? $project->end_date->format('M d, Y') : '—' }}</td>
                        <td class="text-center">{{ $project->tasks_count ?? $project->tasks()->count() }}</td>
                        <td class="text-center">
                            @php $pct = $project->completionPercentage; @endphp
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-grow-1" style="height: 8px;">
                                    <div class="progress-bar {{ $pct === 100 ? 'bg-success' : ($pct > 50 ? 'bg-info' : 'bg-secondary') }}"
                                         role="progressbar" style="width: {{ $pct }}%"></div>
                                </div>
                                <small class="text-muted">{{ $pct }}%</small>
                            </div>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('projects.show', $project) }}" class="btn btn-sm btn-outline-info" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('projects.edit', $project) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            @if($project->status !== 'archived')
                                <form action="{{ route('projects.destroy', $project) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Archive this project?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-secondary" title="Archive">
                                        <i class="fas fa-archive"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-center">
        {{ $projects->withQueryString()->links() }}
    </div>
@else
    <div class="text-center py-5">
        <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
        <h5 class="text-muted">No projects found</h5>
        <p class="text-muted">Get started by creating your first project.</p>
        @can('create', App\Models\Project::class)
            <a href="{{ route('projects.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>Create Project
            </a>
        @endcan
    </div>
@endif
@endsection
