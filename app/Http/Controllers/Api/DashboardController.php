<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    public function admin(Request $request): JsonResponse
    {
        $data = $this->dashboardService->adminDashboard();

        return ApiResponse::success($data);
    }

    public function projectManager(Request $request): JsonResponse
    {
        $data = $this->dashboardService->projectManagerDashboard($request->user()->id);

        return ApiResponse::success($data);
    }

    public function employee(Request $request): JsonResponse
    {
        $data = $this->dashboardService->employeeDashboard($request->user()->id);

        return ApiResponse::success($data);
    }
}
