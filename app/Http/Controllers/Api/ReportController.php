<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    public function projectReport(Request $request, Project $project): JsonResponse
    {
        $user = $request->user();

        if (! $user->hasRole('admin') && ! ($user->hasRole('project_manager') && $project->project_manager_id === $user->id)) {
            return ApiResponse::error('Forbidden. Insufficient permissions.', 403);
        }

        $report = $this->reportService->getProjectReport($project->id);

        return ApiResponse::success($report);
    }

    public function employeeReport(Request $request, User $user): JsonResponse
    {
        $authUser = $request->user();

        if (! $authUser->hasRole('admin') && ! $authUser->hasRole('project_manager')) {
            return ApiResponse::error('Forbidden. Insufficient permissions.', 403);
        }

        $report = $this->reportService->getEmployeeReport($user->id);

        return ApiResponse::success($report);
    }

    public function projectsReport(Request $request): JsonResponse
    {
        if (! $request->user()->hasRole('admin')) {
            return ApiResponse::error('Forbidden. Insufficient permissions.', 403);
        }

        $report = $this->reportService->getAllProjectsReport();

        return ApiResponse::success($report);
    }
}
