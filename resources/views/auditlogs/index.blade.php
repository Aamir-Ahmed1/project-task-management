@extends('layouts.app')
@section('title', 'Audit Logs')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3"><i class="fas fa-history me-2"></i>Audit Logs</h1>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('auditlogs.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label for="action" class="form-label small">Action</label>
                <select name="action" id="action" class="form-select form-select-sm">
                    <option value="">All Actions</option>
                    <option value="created" {{ request('action') === 'created' ? 'selected' : '' }}>Created</option>
                    <option value="updated" {{ request('action') === 'updated' ? 'selected' : '' }}>Updated</option>
                    <option value="deleted" {{ request('action') === 'deleted' ? 'selected' : '' }}>Deleted</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="entity_type" class="form-label small">Entity Type</label>
                <input type="text" name="entity_type" id="entity_type" class="form-control form-control-sm" placeholder="e.g. Task, Project" value="{{ request('entity_type') }}">
            </div>
            <div class="col-md-3">
                <label for="date_from" class="form-label small">Date From</label>
                <input type="date" name="date_from" id="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-3">
                <label for="date_to" class="form-label small">Date To</label>
                <input type="date" name="date_to" id="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter me-1"></i>Filter</button>
                <a href="{{ route('auditlogs.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-times me-1"></i>Clear</a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Timestamp</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Entity</th>
                        <th>Details</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($auditLogs as $log)
                        <tr>
                            <td class="text-nowrap">
                                <small>{{ \Carbon\Carbon::parse($log->created_at)->format('M d, Y H:i:s') }}</small>
                                <br><small class="text-muted">{{ \Carbon\Carbon::parse($log->created_at)->diffForHumans() }}</small>
                            </td>
                            <td>{{ $log->user?->name ?? 'System' }}</td>
                            <td>
                                @php
                                    $actionClasses = ['created' => 'badge bg-success', 'updated' => 'badge bg-info', 'deleted' => 'badge bg-danger'];
                                @endphp
                                <span class="{{ $actionClasses[$log->action] ?? 'badge bg-secondary' }}">{{ ucfirst($log->action) }}</span>
                            </td>
                            <td>
                                <strong>{{ $log->entity_type }}</strong>
                                @if($log->entity_id)
                                    <span class="text-muted">#{{ $log->entity_id }}</span>
                                @endif
                            </td>
                            <td style="max-width: 300px;">
                                @if($log->previous_values || $log->new_values)
                                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#details-{{ $log->id }}">
                                        <i class="fas fa-code me-1"></i>View Diff
                                    </button>
                                    <div class="collapse mt-2" id="details-{{ $log->id }}">
                                        <div class="card card-body bg-dark text-light p-2" style="max-height: 200px; overflow-y: auto; font-size: 11px;">
                                            <pre class="mb-0"><code>@json($log->previous_values ?? [], JSON_PRETTY_PRINT)</code></pre>
                                            @if($log->previous_values && $log->new_values)
                                                <hr class="my-1 border-light">
                                            @endif
                                            <pre class="mb-0"><code>@json($log->new_values ?? [], JSON_PRETTY_PRINT)</code></pre>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td><small class="text-muted">{{ $log->ip_address ?? '—' }}</small></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No audit logs found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($auditLogs->hasPages())
        <div class="card-footer bg-white d-flex justify-content-center">
            {{ $auditLogs->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
