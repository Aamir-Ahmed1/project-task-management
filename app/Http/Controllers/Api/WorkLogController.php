<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\WorkLog;
use App\Services\WorkLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WorkLogController extends Controller
{
    public function __construct(
        protected WorkLogService $workLogService
    ) {}

    public function index(Request $request, Task $task): JsonResponse
    {
        $user = $request->user();

        if ($user->hasRole('employee')) {
            $filters['user_id'] = $user->id;
        }

        $filters = $request->only('user_id', 'date_from', 'date_to', 'per_page');
        $filters['user_id'] = $filters['user_id'] ?? ($user->hasRole('employee') ? $user->id : null);

        $workLogs = $this->workLogService->listByTask($task->id, $filters);

        return ApiResponse::paginated($workLogs);
    }

    public function store(Request $request, Task $task): JsonResponse
    {
        $user = $request->user();

        if ($user->hasRole('employee') && $task->assigned_to !== $user->id) {
            return ApiResponse::error('Forbidden. You can only log time for your own tasks.', 403);
        }

        $validator = Validator::make($request->all(), [
            'description' => 'required|string',
            'hours_worked' => 'required|numeric|min:0.25|max:24',
            'attachment' => 'nullable|string',
            'logged_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Validation failed', 422, $validator->errors());
        }

        $data = $request->all();
        $data['task_id'] = $task->id;
        $data['user_id'] = $user->id;

        $workLog = $this->workLogService->create($data);

        return ApiResponse::success($workLog, 'Work log created successfully', 201);
    }

    public function show(WorkLog $workLog): JsonResponse
    {
        $workLog->load('user:id,name', 'task:id,name,assigned_to,project_id');

        $user = request()->user();

        if ($user->hasRole('employee') && $workLog->user_id !== $user->id) {
            return ApiResponse::error('Forbidden. You can only view your own work logs.', 403);
        }

        return ApiResponse::success($workLog);
    }

    public function replies(WorkLog $workLog): JsonResponse
    {
        $replies = $this->workLogService->getReplies($workLog->id);

        return ApiResponse::success($replies);
    }

    public function addReply(Request $request, WorkLog $workLog): JsonResponse
    {
        $user = $request->user();

        $workLog->load('task.project:id,project_manager_id');

        if (! $user->hasRole('admin') && ! ($user->hasRole('project-manager') && $workLog->task->project->project_manager_id === $user->id)) {
            return ApiResponse::error('Forbidden. Only project managers can reply to work logs.', 403);
        }

        $validator = Validator::make($request->all(), [
            'reply' => 'required|string',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Validation failed', 422, $validator->errors());
        }

        $reply = $this->workLogService->addReply($workLog->id, $user->id, $request->reply);

        return ApiResponse::success($reply, 'Reply added successfully', 201);
    }
}
