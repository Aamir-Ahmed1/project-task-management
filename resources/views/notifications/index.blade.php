@extends('layouts.app')
@section('title', 'Notifications')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3"><i class="fas fa-bell me-2"></i>Notifications</h1>
    <form action="{{ route('notifications.markAllAsRead') }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-outline-primary">
            <i class="fas fa-check-double me-1"></i>Mark All as Read
        </button>
    </form>
</div>

@forelse ($notifications as $notification)
    @php
        $data = $notification->data;
        $isOverdue = str_contains($notification->type, 'TaskOverdue');
        $isReminder = str_contains($notification->type, 'DeadlineReminder');
        $icon = $isOverdue ? 'fa-exclamation-triangle text-danger' : ($isReminder ? 'fa-clock text-warning' : 'fa-bell text-info');
        $isUnread = is_null($notification->read_at);
    @endphp
    <div class="card shadow-sm mb-2 {{ $isUnread ? 'border-start border-4 border-primary' : '' }}">
        <div class="card-body py-3">
            <div class="d-flex align-items-start">
                <div class="me-3 fs-4">
                    <i class="fas {{ $icon }}"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            @if($isUnread)
                                <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-link text-decoration-none p-0 stretched-link">
                                        <strong>{{ $data['message'] ?? 'Notification' }}</strong>
                                    </button>
                                </form>
                            @else
                                <span>{{ $data['message'] ?? 'Notification' }}</span>
                            @endif
                        </div>
                        <small class="text-muted ms-2 text-nowrap">{{ \Carbon\Carbon::parse($notification->created_at)->diffForHumans() }}</small>
                    </div>
                    <div class="mt-1">
                        @if(isset($data['task_name']))
                            <span class="badge bg-secondary me-1"><i class="fas fa-tasks me-1"></i>{{ $data['task_name'] }}</span>
                        @endif
                        @if(isset($data['project_name']))
                            <span class="badge bg-info me-1"><i class="fas fa-folder me-1"></i>{{ $data['project_name'] }}</span>
                        @endif
                        @if(isset($data['deadline']))
                            <span class="badge bg-warning"><i class="fas fa-calendar me-1"></i>{{ $data['deadline'] }}</span>
                        @endif
                    </div>
                </div>
                @if($isUnread)
                    <span class="badge bg-danger rounded-pill ms-2" style="width: 10px; height: 10px; padding: 0;">&nbsp;</span>
                @endif
            </div>
        </div>
    </div>
@empty
    <div class="text-center py-5">
        <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
        <h5 class="text-muted">No notifications</h5>
        <p class="text-muted">You're all caught up!</p>
    </div>
@endforelse

@if($notifications->hasPages())
    <div class="d-flex justify-content-center mt-3">
        {{ $notifications->links() }}
    </div>
@endif
@endsection
