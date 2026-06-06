<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    )
    {
        $this->middleware('auth');
    }

    public function projects(): View
    {
        $data = $this->reportService->getAllProjectsReport();

        return view('reports.projects', $data);
    }

    public function project(int $id): View
    {
        $data = $this->reportService->getProjectReport($id);

        return view('reports.project', $data);
    }

    public function employee(int $id): View
    {
        $data = $this->reportService->getEmployeeReport($id);

        return view('reports.employee', $data);
    }
}
