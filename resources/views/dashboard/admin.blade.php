@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-tachometer-alt me-2"></i>Admin Dashboard</h1>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card text-bg-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Total Projects</h6>
                        <h2 class="mb-0">{{ isset($total_projects) ? $total_projects : 0 }}</h2>
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
                        <h6 class="card-title mb-0">Total Tasks</h6>
                        <h2 class="mb-0">{{ isset($total_tasks) ? $total_tasks : 0 }}</h2>
                    </div>
                    <i class="fas fa-list-check fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-bg-info">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Active Employees</h6>
                        <h2 class="mb-0">{{ isset($active_employees) ? $active_employees : 0 }}</h2>
                    </div>
                    <i class="fas fa-users fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-bg-danger">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Overdue Tasks</h6>
                        <h2 class="mb-0">{{ isset($overdue_tasks) ? $overdue_tasks : 0 }}</h2>
                    </div>
                    <i class="fas fa-exclamation-triangle fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Tasks by Status</h5>
            </div>
            <div class="card-body">
                @php
                    $statusColors = [
                        'to_do' => 'secondary',
                        'in_progress' => 'primary',
                        'in_review' => 'info',
                        'completed' => 'success',
                        'blocked' => 'dark',
                    ];
                    $statusLabels = [
                        'to_do' => 'To Do',
                        'in_progress' => 'In Progress',
                        'in_review' => 'In Review',
                        'completed' => 'Completed',
                        'blocked' => 'Blocked',
                    ];
                    $statusData = isset($tasks_by_status) ? $tasks_by_status : [];
                @endphp
                @forelse ($statusData as $status => $count)
                    @php
                        $total = array_sum($statusData) ?: 1;
                        $percent = round(($count / $total) * 100);
                        $color = $statusColors[$status] ?? 'secondary';
                        $label = $statusLabels[$status] ?? ucfirst($status);
                    @endphp
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>{{ $label }}</span>
                            <span>{{ $count }} ({{ $percent }}%)</span>
                        </div>
                        <div class="progress" style="height: 24px;">
                            <div class="progress-bar bg-{{ $color }}" role="progressbar" style="width: {{ $percent }}%">{{ $percent }}%</div>
                        </div>
                    </div>
                @empty
                    <p class="text-muted mb-0">No tasks found.</p>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Overview</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Active Projects
                        <span class="badge bg-primary rounded-pill">{{ $active_projects ?? 0 }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Completed Tasks
                        <span class="badge bg-success rounded-pill">{{ $completed_tasks ?? 0 }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Project Managers
                        <span class="badge bg-info rounded-pill">{{ $project_managers ?? 0 }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Total Hours Logged
                        <span class="badge bg-secondary rounded-pill">{{ number_format($total_hours_logged ?? 0, 1) }}h</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Archived Projects
                        <span class="badge bg-warning rounded-pill">{{ $archived_projects ?? 0 }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-folder-open me-2"></i>Recent Projects</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Tasks</th>
                        <th>Completion</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $statusClasses = ['planning' => 'secondary', 'active' => 'primary', 'completed' => 'success', 'archived' => 'warning'];
                    @endphp
                    @forelse ($recent_projects ?? [] as $project)
                        <tr>
                            <td>{{ $project['name'] }}</td>
                            <td><span class="badge bg-{{ $statusClasses[$project['status']] ?? 'secondary' }}">{{ ucfirst($project['status']) }}</span></td>
                            <td>{{ $project['tasks_count'] ?? 0 }}</td>
                            <td>
                                @php $pct = $project['completion_percentage'] ?? 0; @endphp
                                <div class="d-flex align-items-center">
                                    <div class="progress flex-grow-1 me-2" style="height: 20px;">
                                        <div class="progress-bar bg-{{ $pct >= 100 ? 'success' : ($pct >= 50 ? 'primary' : 'warning') }}" style="width: {{ $pct }}%">{{ $pct }}%</div>
                                    </div>
                                    <span>{{ $pct }}%</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted">No projects found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
