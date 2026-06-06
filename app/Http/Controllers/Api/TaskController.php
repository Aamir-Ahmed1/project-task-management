<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    public function __construct(
        protected TaskService $taskService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $filters = $request->only('status', 'priority', 'assignee_id', 'project_id', 'deadline_from', 'deadline_to', 'overdue', 'due_soon', 'due_soon_hours', 'search', 'per_page');

        if ($user->hasRole('employee')) {
            $filters['assignee_id'] = $user->id;
        } elseif ($user->hasRole('project_manager')) {
            $managedProjectIds = $user->managedProjects()->pluck('id')->toArray();
            if (! empty($filters['project_id']) && ! in_array($filters['project_id'], $managedProjectIds)) {
                return ApiResponse::error('Forbidden. You can only view tasks from your projects.', 403);
            }
            if (empty($filters['project_id'])) {
                $tasks = Task::whereIn('project_id', $managedProjectIds);
                $filters['per_page'] = $filters['per_page'] ?? 15;
                $perPage = $filters['per_page'];
                $tasks = $tasks->with(['project:id,name', 'assignedUser:id,name'])
                    ->orderBy('created_at', 'desc')
                    ->paginate($perPage);

                return ApiResponse::paginated($tasks);
            }
        }

        $tasks = $this->taskService->list($filters);

        return ApiResponse::paginated($tasks);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->hasRole('admin') && ! $user->hasRole('project_manager')) {
            return ApiResponse::error('Forbidden. Insufficient permissions.', 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|string|in:low,medium,high,critical',
            'status' => 'nullable|string|in:pending,in_progress,review,completed',
            'deadline' => 'nullable|date',
            'estimated_hours' => 'nullable|numeric|min:0',
            'project_id' => 'required|exists:projects,id',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Validation failed', 422, $validator->errors());
        }

        $data = $request->all();
        $data['created_by'] = $user->id;

        if ($user->hasRole('project_manager')) {
            $project = Project::find($data['project_id']);
            if (! $project || $project->project_manager_id !== $user->id) {
                return ApiResponse::error('Forbidden. You can only create tasks in your projects.', 403);
            }
        }

        $task = $this->taskService->create($data);

        return ApiResponse::success($task, 'Task created successfully', 201);
    }

    public function show(Task $task): JsonResponse
    {
        $task->load('project:id,name,project_manager_id');

        $user = request()->user();

        if ($user->hasRole('employee') && $task->assigned_to !== $user->id) {
            return ApiResponse::error('Forbidden. You can only view your own tasks.', 403);
        }

        if ($user->hasRole('project_manager') && $task->project->project_manager_id !== $user->id) {
            return ApiResponse::error('Forbidden. You can only view tasks from your projects.', 403);
        }

        $result = $this->taskService->show($task);

        return ApiResponse::success($result);
    }

    public function update(Request $request, Task $task): JsonResponse
    {
        $user = $request->user();

        $task->load('project:id,project_manager_id');

        if (! $user->hasRole('admin') && ! ($user->hasRole('project_manager') && $task->project->project_manager_id === $user->id)) {
            return ApiResponse::error('Forbidden. Insufficient permissions.', 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|string|in:low,medium,high,critical',
            'status' => 'nullable|string|in:pending,in_progress,review,completed',
            'deadline' => 'nullable|date',
            'estimated_hours' => 'nullable|numeric|min:0',
            'actual_hours' => 'nullable|numeric|min:0',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Validation failed', 422, $validator->errors());
        }

        $task = $this->taskService->update($task, $request->all());

        return ApiResponse::success($task, 'Task updated successfully');
    }

    public function destroy(Request $request, Task $task): JsonResponse
    {
        if (! $request->user()->hasRole('admin')) {
            return ApiResponse::error('Forbidden. Insufficient permissions.', 403);
        }

        $task->delete();

        return ApiResponse::success(null, 'Task deleted successfully');
    }

    public function updateStatus(Request $request, Task $task): JsonResponse
    {
        $user = $request->user();

        $task->load('project:id,project_manager_id');

        $canUpdate = $user->hasRole('admin')
            || ($user->hasRole('project_manager') && $task->project->project_manager_id === $user->id)
            || $task->assigned_to === $user->id;

        if (! $canUpdate) {
            return ApiResponse::error('Forbidden. Insufficient permissions.', 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:pending,in_progress,review,completed',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Validation failed', 422, $validator->errors());
        }

        $task = $this->taskService->update($task, ['status' => $request->status]);

        return ApiResponse::success($task, 'Task status updated successfully');
    }

    public function assign(Request $request, Task $task): JsonResponse
    {
        $user = $request->user();

        if (! $user->hasRole('admin') && ! $user->hasRole('project_manager')) {
            return ApiResponse::error('Forbidden. Insufficient permissions.', 403);
        }

        $validator = Validator::make($request->all(), [
            'assigned_to' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Validation failed', 422, $validator->errors());
        }

        $task = $this->taskService->update($task, ['assigned_to' => $request->assigned_to]);

        return ApiResponse::success($task, 'Task assigned successfully');
    }

    public function timeline(Task $task): JsonResponse
    {
        $task->load('project:id,project_manager_id');

        $user = request()->user();

        if ($user->hasRole('employee') && $task->assigned_to !== $user->id) {
            return ApiResponse::error('Forbidden. You can only view your own tasks.', 403);
        }

        if ($user->hasRole('project_manager') && $task->project->project_manager_id !== $user->id) {
            return ApiResponse::error('Forbidden. You can only view tasks from your projects.', 403);
        }

        $timeline = $this->taskService->getTimeline($task);

        return ApiResponse::success($timeline);
    }
}
