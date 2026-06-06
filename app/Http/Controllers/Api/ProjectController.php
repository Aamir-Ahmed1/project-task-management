<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\ProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProjectController extends Controller
{
    public function __construct(
        protected ProjectService $projectService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasRole('employee')) {
            return ApiResponse::success([], 'No projects found');
        }

        $filters = $request->only('status', 'manager_id', 'date_from', 'date_to', 'search', 'per_page');

        if ($user->hasRole('project_manager')) {
            $filters['manager_id'] = $user->id;
        }

        $projects = $this->projectService->list($filters);

        return ApiResponse::paginated($projects);
    }

    public function store(Request $request): JsonResponse
    {
        if (! $request->user()->hasRole('admin')) {
            return ApiResponse::error('Forbidden. Insufficient permissions.', 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'nullable|string|in:planning,active,completed,archived',
            'project_manager_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Validation failed', 422, $validator->errors());
        }

        $data = $request->all();
        $data['created_by'] = $request->user()->id;

        $project = $this->projectService->create($data);

        return ApiResponse::success($project, 'Project created successfully', 201);
    }

    public function show(Project $project): JsonResponse
    {
        $result = $this->projectService->show($project);

        return ApiResponse::success($result);
    }

    public function update(Request $request, Project $project): JsonResponse
    {
        $user = $request->user();

        if (! $user->hasRole('admin') && ! ($user->hasRole('project_manager') && $project->project_manager_id === $user->id)) {
            return ApiResponse::error('Forbidden. Insufficient permissions.', 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'nullable|string|in:planning,active,completed,archived',
            'project_manager_id' => 'sometimes|exists:users,id',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Validation failed', 422, $validator->errors());
        }

        $project = $this->projectService->update($project, $request->all());

        return ApiResponse::success($project, 'Project updated successfully');
    }

    public function destroy(Request $request, Project $project): JsonResponse
    {
        if (! $request->user()->hasRole('admin')) {
            return ApiResponse::error('Forbidden. Insufficient permissions.', 403);
        }

        $project->delete();

        return ApiResponse::success(null, 'Project deleted successfully');
    }

    public function archive(Request $request, Project $project): JsonResponse
    {
        $user = $request->user();

        if (! $user->hasRole('admin') && ! ($user->hasRole('project_manager') && $project->project_manager_id === $user->id)) {
            return ApiResponse::error('Forbidden. Insufficient permissions.', 403);
        }

        $this->projectService->archive($project);

        return ApiResponse::success(null, 'Project archived successfully');
    }

    public function progress(Project $project): JsonResponse
    {
        $progress = $this->projectService->getProjectProgress($project);

        return ApiResponse::success($progress);
    }
}
