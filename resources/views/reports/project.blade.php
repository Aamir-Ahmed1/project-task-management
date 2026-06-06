@extends('layouts.app')
@section('title', 'Project Report')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3"><i class="fas fa-chart-pie me-2"></i>Project Report: {{ $project_name ?? 'N/A' }}</h1>
    <a href="{{ route('reports.projects') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Back to Reports
    </a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card text-bg-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Completion</h6>
                        <h2 class="mb-0">{{ $completion_percentage ?? 0 }}%</h2>
                    </div>
                    <i class="fas fa-check-circle fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-bg-info">
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
    <div class="col-md-3">
        <div class="card text-bg-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Completed</h6>
                        <h2 class="mb-0">{{ $completed_tasks ?? 0 }}</h2>
                    </div>
                    <i class="fas fa-check fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-bg-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Pending</h6>
                        <h2 class="mb-0">{{ $pending_tasks ?? 0 }}</h2>
                    </div>
                    <i class="fas fa-clock fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Tasks by Status</h5>
            </div>
            <div class="card-body">
                @php
                    $statusColors = ['to_do' => 'secondary', 'in_progress' => 'primary', 'in_review' => 'info', 'completed' => 'success', 'blocked' => 'dark'];
                    $statusLabels = ['to_do' => 'To Do', 'in_progress' => 'In Progress', 'in_review' => 'In Review', 'completed' => 'Completed', 'blocked' => 'Blocked'];
                    $statusData = isset($tasks_by_status) ? $tasks_by_status : [];
                @endphp
                @forelse ($statusData as $status => $count)
                    @php
                        $total = array_sum($statusData) ?: 1;
                        $percent = round(($count / $total) * 100);
                    @endphp
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>{{ $statusLabels[$status] ?? ucfirst($status) }}</span>
                            <span>{{ $count }} ({{ $percent }}%)</span>
                        </div>
                        <div class="progress" style="height: 24px;">
                            <div class="progress-bar bg-{{ $statusColors[$status] ?? 'secondary' }}" role="progressbar" style="width: {{ $percent }}%">{{ $percent }}%</div>
                        </div>
                    </div>
                @empty
                    <p class="text-muted mb-0">No tasks found.</p>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Overview</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Status
                        @php
                            $sClasses = ['planning' => 'badge bg-secondary', 'active' => 'badge bg-primary', 'completed' => 'badge bg-success', 'archived' => 'badge bg-warning'];
                        @endphp
                        <span class="{{ $sClasses[$project_status ?? ''] ?? 'badge bg-secondary' }}">{{ ucfirst($project_status ?? 'N/A') }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Completion
                        <span class="badge bg-info rounded-pill">{{ $completion_percentage ?? 0 }}%</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Completed Tasks
                        <span class="badge bg-success rounded-pill">{{ $completed_tasks ?? 0 }} / {{ $total_tasks ?? 0 }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Pending Tasks
                        <span class="badge bg-warning rounded-pill">{{ $pending_tasks ?? 0 }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Total Hours Logged
                        <span class="badge bg-secondary rounded-pill">{{ number_format($total_hours_logged ?? 0, 1) }}h</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fas fa-tasks me-2"></i>Completion Overview</h5>
    </div>
    <div class="card-body">
        @php $pct = $completion_percentage ?? 0; @endphp
        <div class="d-flex align-items-center gap-3 mb-2">
            <div class="progress flex-grow-1" style="height: 30px;">
                <div class="progress-bar {{ $pct >= 100 ? 'bg-success' : ($pct >= 50 ? 'bg-info' : 'bg-warning') }}" role="progressbar" style="width: {{ $pct }}%">{{ $pct }}%</div>
            </div>
            <strong>{{ $completed_tasks ?? 0 }}/{{ $total_tasks ?? 0 }}</strong>
        </div>
    </div>
</div>
@endsection
