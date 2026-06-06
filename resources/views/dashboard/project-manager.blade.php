@extends('layouts.app')

@section('title', 'Project Manager Dashboard')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-tachometer-alt me-2"></i>Project Manager Dashboard</h1>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card text-bg-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Managed Projects</h6>
                        <h2 class="mb-0">{{ isset($total_managed_projects) ? $total_managed_projects : 0 }}</h2>
                    </div>
                    <i class="fas fa-folder fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-bg-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Active Tasks</h6>
                        <h2 class="mb-0">{{ isset($active_tasks) ? $active_tasks : 0 }}</h2>
                    </div>
                    <i class="fas fa-spinner fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-bg-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Completed Tasks</h6>
                        <h2 class="mb-0">{{ isset($completed_tasks) ? $completed_tasks : 0 }}</h2>
                    </div>
                    <i class="fas fa-check-circle fa-3x opacity-50"></i>
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
                        <h2 class="mb-0">{{ isset($overdue_tasks_count) ? $overdue_tasks_count : 0 }}</h2>
                    </div>
                    <i class="fas fa-exclamation-triangle fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-folder-open me-2"></i>Project Summaries</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Project</th>
                                <th>Status</th>
                                <th>Tasks</th>
                                <th>Completion</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $statusClasses = ['planning' => 'secondary', 'active' => 'primary', 'completed' => 'success', 'archived' => 'warning'];
                            @endphp
                            @forelse ($managed_projects ?? [] as $project)
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

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-users me-2"></i>Employee Productivity</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Employee</th>
                                <th>Assigned</th>
                                <th>Completed</th>
                                <th>Productivity</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($employee_productivity ?? [] as $emp)
                                @php $pct = ($emp['assigned_tasks'] > 0) ? round(($emp['completed_tasks'] / $emp['assigned_tasks']) * 100) : 0; @endphp
                                <tr>
                                    <td>{{ $emp['name'] }}</td>
                                    <td>{{ $emp['assigned_tasks'] }}</td>
                                    <td>{{ $emp['completed_tasks'] }}</td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-{{ $pct >= 75 ? 'success' : ($pct >= 40 ? 'primary' : 'warning') }}" style="width: {{ $pct }}%">{{ $pct }}%</div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted">No employee data found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Upcoming Deadlines</h5>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse ($upcoming_deadlines ?? [] as $task)
                        @php
                            $daysRemaining = now()->diffInDays(\Carbon\Carbon::parse($task['deadline']), false);
                            $badgeClass = $daysRemaining <= 1 ? 'bg-danger' : ($daysRemaining <= 3 ? 'bg-warning' : 'bg-info');
                        @endphp
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong>{{ $task['name'] }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        <i class="fas fa-folder me-1"></i>{{ $task['project_name'] ?? 'N/A' }}
                                        @if ($task['assigned_user'])
                                            &middot; <i class="fas fa-user me-1"></i>{{ $task['assigned_user'] }}
                                        @endif
                                    </small>
                                </div>
                                <span class="badge {{ $badgeClass }}">{{ $daysRemaining }}d</span>
                            </div>
                            <small class="text-muted">Deadline: {{ $task['deadline'] }}</small>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-muted">No upcoming deadlines.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        @if (isset($overdue_tasks) && count($overdue_tasks) > 0)
            <div class="card mt-3">
                <div class="card-header text-bg-danger">
                    <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Overdue Tasks</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @foreach ($overdue_tasks as $task)
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <strong>{{ $task['name'] }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $task['project_name'] ?? 'N/A' }}</small>
                                    </div>
                                    <span class="badge bg-danger">{{ $task['deadline'] }}</span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
