<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService
    )
    {
        $this->middleware('auth');
    }

    public function admin(): View
    {
        $data = $this->dashboardService->adminDashboard();

        return view('dashboard.admin', $data);
    }

    public function projectManager(): View
    {
        $data = $this->dashboardService->projectManagerDashboard(auth()->id());

        return view('dashboard.project-manager', $data);
    }

    public function employee(): View
    {
        $data = $this->dashboardService->employeeDashboard(auth()->id());

        return view('dashboard.employee', $data);
    }
}
