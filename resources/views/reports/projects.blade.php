@extends('layouts.app')
@section('title', 'Projects Report')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3"><i class="fas fa-chart-bar me-2"></i>Projects Report</h1>
    <a href="{{ route('dashboard.admin') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Back
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card text-bg-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Total Projects</h6>
                        <h2 class="mb-0">{{ $total_projects ?? 0 }}</h2>
                    </div>
                    <i class="fas fa-folder fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-bg-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Active</h6>
                        <h2 class="mb-0">{{ $active_projects ?? 0 }}</h2>
                    </div>
                    <i class="fas fa-play-circle fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-bg-secondary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Archived</h6>
                        <h2 class="mb-0">{{ $archived_projects ?? 0 }}</h2>
                    </div>
                    <i class="fas fa-archive fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-bg-info">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Completion Rate</h6>
                        <h2 class="mb-0">{{ isset($average_completion_percentage) ? $average_completion_percentage : 0 }}%</h2>
                    </div>
                    <i class="fas fa-percentage fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card text-bg-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Total Tasks</h6>
                        <h2 class="mb-0">{{ $total_tasks ?? 0 }}</h2>
                    </div>
                    <i class="fas fa-list-check fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card text-bg-danger">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Hours Logged</h6>
                        <h2 class="mb-0">{{ number_format($total_hours_logged ?? 0, 1) }}</h2>
                    </div>
                    <i class="fas fa-clock fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fas fa-table me-2"></i>All Projects</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Project</th>
                        <th>Status</th>
                        <th class="text-center">Tasks</th>
                        <th class="text-center">Completion</th>
                        <th class="text-end">Hours Logged</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $statusClasses = ['planning' => 'bg-secondary', 'active' => 'bg-primary', 'completed' => 'bg-success', 'archived' => 'bg-warning'];
                    @endphp
                    @forelse ($projects ?? [] as $project)
                        <tr>
                            <td>
                                <a href="{{ route('reports.project', $project['id']) }}" class="text-decoration-none fw-semibold">
                                    {{ $project['name'] }}
                                </a>
                            </td>
                            <td><span class="badge {{ $statusClasses[$project['status']] ?? 'bg-secondary' }}">{{ ucfirst($project['status']) }}</span></td>
                            <td class="text-center">{{ $project['tasks_count'] ?? 0 }}</td>
                            <td class="text-center">
                                @php $pct = $project['completion_percentage'] ?? 0; @endphp
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height: 8px;">
                                        <div class="progress-bar {{ $pct === 100 ? 'bg-success' : ($pct > 50 ? 'bg-info' : 'bg-secondary') }}" role="progressbar" style="width: {{ $pct }}%"></div>
                                    </div>
                                    <small class="text-muted">{{ $pct }}%</small>
                                </div>
                            </td>
                            <td class="text-end text-muted">—</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No projects found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
