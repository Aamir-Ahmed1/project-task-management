<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectRequest;
use App\Models\Project;
use App\Services\ProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

        if ($user->hasRole('project-manager')) {
            $filters['manager_id'] = $user->id;
        }

        $projects = $this->projectService->list($filters);

        return ApiResponse::paginated($projects);
    }

    public function store(ProjectRequest $request): JsonResponse
    {
        if (! $request->user()->hasRole('admin')) {
            return ApiResponse::error('Forbidden. Insufficient permissions.', 403);
        }

        $data = $request->validated();
        $data['created_by'] = $request->user()->id;

        $project = $this->projectService->create($data);

        return ApiResponse::success($project, 'Project created successfully', 201);
    }

    public function show(Project $project): JsonResponse
    {
        $result = $this->projectService->show($project);

        return ApiResponse::success($result);
    }

    public function update(ProjectRequest $request, Project $project): JsonResponse
    {
        $user = $request->user();

        if (! $user->hasRole('admin') && ! ($user->hasRole('project-manager') && $project->project_manager_id === $user->id)) {
            return ApiResponse::error('Forbidden. Insufficient permissions.', 403);
        }

        $project = $this->projectService->update($project, $request->validated());

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

        if (! $user->hasRole('admin') && ! ($user->hasRole('project-manager') && $project->project_manager_id === $user->id)) {
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
