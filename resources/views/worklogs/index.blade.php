@extends('layouts.app')
@section('title', 'Work Logs')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3"><i class="fas fa-hourglass-half me-2"></i>Work Logs</h1>
    <a href="{{ route('tasks.show', $task) }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Back to Task
    </a>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <h5 class="card-title mb-0">{{ $task->name }}</h5>
        <small class="text-muted">
            @if($task->project)
                <a href="{{ route('projects.show', $task->project) }}" class="text-decoration-none">{{ $task->project->name }}</a>
            @else
                No project
            @endif
        </small>
    </div>
</div>

@php
    $isAssigned = auth()->id() === $task->assigned_to;
    $isPM = auth()->user()->roles->first()?->name === 'project-manager';
    $isAdmin = auth()->user()->roles->first()?->name === 'admin';
@endphp

@if($isAssigned || $isPM || $isAdmin)
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Add Work Log</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('worklogs.store', $task) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" id="description" rows="3" class="form-control @error('description') is-invalid @enderror" required>{{ old('description') }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="hours_worked" class="form-label">Hours</label>
                        <input type="number" step="0.25" min="0" name="hours_worked" id="hours_worked" class="form-control @error('hours_worked') is-invalid @enderror" value="{{ old('hours_worked') }}" required>
                        @error('hours_worked')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-8 mb-3">
                        <label for="attachment" class="form-label">Attachment (URL)</label>
                        <input type="url" name="attachment" id="attachment" class="form-control @error('attachment') is-invalid @enderror" value="{{ old('attachment') }}" placeholder="https://...">
                        @error('attachment')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Work Log</button>
            </form>
        </div>
    </div>
@endif

<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Work Log Entries</h5>
    </div>
    <div class="card-body p-0">
        @forelse ($workLogs as $log)
            <div class="border-bottom p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <strong>{{ $log->user?->name ?? '—' }}</strong>
                        <small class="text-muted ms-2">{{ \Carbon\Carbon::parse($log->logged_at)->diffForHumans() }}</small>
                        <span class="badge bg-secondary ms-2">{{ number_format($log->hours_worked, 1) }}h</span>
                    </div>
                    <div>
                        @if($log->attachment)
                            <a href="{{ $log->attachment }}" target="_blank" class="btn btn-sm btn-outline-info" title="Attachment">
                                <i class="fas fa-paperclip"></i>
                            </a>
                        @endif
                        <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#replies-{{ $log->id }}">
                            <i class="fas fa-comment me-1"></i>Replies ({{ $log->replies->count() }})
                        </button>
                    </div>
                </div>
                <p class="mb-1 mt-2">{{ $log->description }}</p>
                <small class="text-muted">{{ $log->logged_at?->format('M d, Y H:i') ?? $log->created_at->format('M d, Y H:i') }}</small>

                <div class="collapse mt-3" id="replies-{{ $log->id }}">
                    <div class="card card-body bg-light">
                        @forelse ($log->replies as $reply)
                            <div class="mb-2 pb-2 border-bottom">
                                <strong>{{ $reply->user?->name ?? '—' }}</strong>
                                <small class="text-muted ms-2">{{ \Carbon\Carbon::parse($reply->created_at)->diffForHumans() }}</small>
                                <p class="mb-0 mt-1">{{ $reply->reply }}</p>
                            </div>
                        @empty
                            <p class="text-muted mb-2">No replies yet.</p>
                        @endforelse
                        @if($isPM || $isAdmin)
                            <form action="{{ route('worklogs.addReply', $log) }}" method="POST" class="mt-2">
                                @csrf
                                <div class="input-group">
                                    <textarea name="reply" class="form-control" rows="1" placeholder="Add a reply..." required></textarea>
                                    <button class="btn btn-primary" type="submit"><i class="fas fa-paper-plane"></i></button>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-5">
                <i class="fas fa-hourglass-start fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No work logs yet</h5>
                <p class="text-muted">Be the first to log your work on this task.</p>
            </div>
        @endforelse
    </div>
    @if($workLogs->hasPages())
        <div class="card-footer bg-white d-flex justify-content-center">
            {{ $workLogs->links() }}
        </div>
    @endif
</div>
@endsection
