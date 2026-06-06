@extends('layouts.app')
@section('title', 'Employee Report')

@section('content')
@php
    $employee = isset($user_id) ? \App\Models\User::find($user_id) : null;
@endphp

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3"><i class="fas fa-user me-2"></i>Employee Report: {{ $employee?->name ?? 'User #' . ($user_id ?? 'N/A') }}</h1>
    <a href="{{ route('reports.projects') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Back to Reports
    </a>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex align-items-center gap-3">
            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 64px; height: 64px; font-size: 24px;">
                {{ strtoupper(substr($employee?->name ?? '?', 0, 1)) }}
            </div>
            <div>
                <h4 class="mb-1">{{ $employee?->name ?? 'Unknown User' }}</h4>
                <span class="text-muted">{{ $employee?->email ?? '' }}</span>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card text-bg-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Assigned Tasks</h6>
                        <h2 class="mb-0">{{ $total_assigned_tasks ?? 0 }}</h2>
                    </div>
                    <i class="fas fa-list-check fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-bg-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Completed</h6>
                        <h2 class="mb-0">{{ $completed_tasks ?? 0 }}</h2>
                    </div>
                    <i class="fas fa-check-circle fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
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
    <div class="col-md-3">
        <div class="card text-bg-danger">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Overdue</h6>
                        <h2 class="mb-0">{{ $overdue_tasks ?? 0 }}</h2>
                    </div>
                    <i class="fas fa-exclamation-triangle fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-bg-info">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Avg Completion</h6>
                        <h2 class="mb-0">{{ isset($average_completion_time_hours) ? number_format($average_completion_time_hours, 1) : '—' }}<small>h</small></h2>
                    </div>
                    <i class="fas fa-hourglass-half fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card text-bg-secondary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Total Hours Logged</h6>
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
            <p class="text-muted mb-0">No tasks found for this employee.</p>
        @endforelse
    </div>
</div>
@endsection
