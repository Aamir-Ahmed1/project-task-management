@extends('layouts.app')

@section('title', 'Employee Dashboard')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-tachometer-alt me-2"></i>Employee Dashboard</h1>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card text-bg-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Assigned Tasks</h6>
                        <h2 class="mb-0">{{ isset($total_assigned_tasks) ? $total_assigned_tasks : 0 }}</h2>
                    </div>
                    <i class="fas fa-tasks fa-3x opacity-50"></i>
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
                        <h2 class="mb-0">{{ isset($completed_tasks) ? $completed_tasks : 0 }}</h2>
                    </div>
                    <i class="fas fa-check-circle fa-3x opacity-50"></i>
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
                        <h2 class="mb-0">{{ isset($pending_tasks) ? $pending_tasks : 0 }}</h2>
                    </div>
                    <i class="fas fa-hourglass-half fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-bg-danger">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title mb-0">Overdue</h6>
                        <h2 class="mb-0">{{ isset($overdue_tasks) ? $overdue_tasks : 0 }}</h2>
                    </div>
                    <i class="fas fa-exclamation-triangle fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Tasks Due Soon</h5>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse ($tasks_due_soon ?? [] as $task)
                        @php
                            $deadline = \Carbon\Carbon::parse($task['deadline']);
                            $daysDiff = now()->startOfDay()->diffInDays($deadline, false);
                            $badgeClass = $daysDiff <= 0 ? 'bg-danger' : ($daysDiff <= 2 ? 'bg-warning' : 'bg-info');
                            $badgeText = $daysDiff <= 0 ? 'Overdue' : $daysDiff . 'd left';
                        @endphp
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong>{{ $task['name'] }}</strong>
                                    @if (isset($task['project']))
                                        <br><small class="text-muted"><i class="fas fa-folder me-1"></i>{{ $task['project']['name'] }}</small>
                                    @endif
                                </div>
                                <span class="badge {{ $badgeClass }}">{{ $badgeText }}</span>
                            </div>
                            <small class="text-muted">Deadline: {{ $task['deadline'] }}</small>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-muted">No tasks due soon.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Tasks by Status</h5>
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
                    <div class="mb-2">
                        <div class="d-flex justify-content-between small">
                            <span>{{ $label }}</span>
                            <span>{{ $count }}</span>
                        </div>
                        <div class="progress" style="height: 16px;">
                            <div class="progress-bar bg-{{ $color }}" style="width: {{ $percent }}%">{{ $percent }}%</div>
                        </div>
                    </div>
                @empty
                    <p class="text-muted mb-0">No tasks found.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Activity</h5>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse ($recent_activity ?? [] as $log)
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong>{{ $log['task_name'] ?? 'Unknown Task' }}</strong>
                                    <br>
                                    <small class="text-muted">{{ \Illuminate\Support\Str::limit($log['description'], 80) }}</small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-secondary">{{ $log['hours_worked'] }}h</span>
                                    <br>
                                    <small class="text-muted">{{ $log['logged_at'] }}</small>
                                </div>
                            </div>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-muted">No recent activity.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Summary</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <div class="border rounded p-3 text-center">
                            <h6 class="text-muted mb-1">Total Hours Logged</h6>
                            <h3 class="mb-0 text-primary">{{ number_format($total_hours_logged ?? 0, 1) }}h</h3>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded p-3 text-center">
                            <h6 class="text-muted mb-1">Completion Rate</h6>
                            @php
                                $assigned = $total_assigned_tasks ?? 0;
                                $completed = $completed_tasks ?? 0;
                                $rate = $assigned > 0 ? round(($completed / $assigned) * 100) : 0;
                            @endphp
                            <h3 class="mb-0 {{ $rate >= 75 ? 'text-success' : ($rate >= 40 ? 'text-primary' : 'text-warning') }}">{{ $rate }}%</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
