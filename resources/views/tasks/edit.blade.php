@extends('layouts.app')
@section('title', 'Edit Task')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3"><i class="fas fa-edit me-2"></i>Edit Task</h1>
    <a href="{{ route('tasks.show', $task) }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Back
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form action="{{ route('tasks.update', $task) }}" method="POST">
            @csrf @method('PUT')

            <div class="mb-3">
                <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                <input type="text" name="name" id="name"
                       class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name', $task->name) }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea name="description" id="description" rows="4"
                          class="form-control @error('description') is-invalid @enderror">{{ old('description', $task->description) }}</textarea>
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="priority" class="form-label">Priority</label>
                    <select name="priority" id="priority"
                            class="form-select @error('priority') is-invalid @enderror">
                        <option value="low" {{ old('priority', $task->priority) === 'low' ? 'selected' : '' }}>Low</option>
                        <option value="medium" {{ old('priority', $task->priority) === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="high" {{ old('priority', $task->priority) === 'high' ? 'selected' : '' }}>High</option>
                        <option value="critical" {{ old('priority', $task->priority) === 'critical' ? 'selected' : '' }}>Critical</option>
                    </select>
                    @error('priority')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status"
                            class="form-select @error('status') is-invalid @enderror">
                        <option value="to_do" {{ old('status', $task->status) === 'to_do' ? 'selected' : '' }}>To Do</option>
                        <option value="in_progress" {{ old('status', $task->status) === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="in_review" {{ old('status', $task->status) === 'in_review' ? 'selected' : '' }}>In Review</option>
                        <option value="completed" {{ old('status', $task->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="blocked" {{ old('status', $task->status) === 'blocked' ? 'selected' : '' }}>Blocked</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label for="deadline" class="form-label">Deadline</label>
                    <input type="date" name="deadline" id="deadline"
                           class="form-control @error('deadline') is-invalid @enderror"
                           value="{{ old('deadline', $task->deadline?->format('Y-m-d')) }}">
                    @error('deadline')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="estimated_hours" class="form-label">Estimated Hours</label>
                    <input type="number" step="0.01" min="0" name="estimated_hours" id="estimated_hours"
                           class="form-control @error('estimated_hours') is-invalid @enderror"
                           value="{{ old('estimated_hours', $task->estimated_hours) }}">
                    @error('estimated_hours')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label for="actual_hours" class="form-label">Actual Hours</label>
                    <input type="number" step="0.01" min="0" name="actual_hours" id="actual_hours"
                           class="form-control @error('actual_hours') is-invalid @enderror"
                           value="{{ old('actual_hours', $task->actual_hours) }}">
                    @error('actual_hours')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label for="project_id" class="form-label">Project <span class="text-danger">*</span></label>
                    <select name="project_id" id="project_id"
                            class="form-select @error('project_id') is-invalid @enderror" required>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" {{ old('project_id', $task->project_id) == $project->id ? 'selected' : '' }}>
                                {{ $project->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('project_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-3">
                <label for="assigned_to" class="form-label">Assign To</label>
                <select name="assigned_to" id="assigned_to"
                        class="form-select @error('assigned_to') is-invalid @enderror">
                    <option value="">Unassigned</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ old('assigned_to', $task->assigned_to) == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
                @error('assigned_to')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('tasks.show', $task) }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Update Task
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
