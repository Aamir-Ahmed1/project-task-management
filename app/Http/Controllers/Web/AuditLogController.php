<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function __construct(
        protected AuditLogService $auditLogService
    )
    {
        $this->middleware('auth');
    }

    public function index(Request $request): View
    {
        $filters = $request->only(['user_id', 'action', 'entity_type', 'entity_id', 'date_from', 'date_to', 'per_page']);
        $auditLogs = $this->auditLogService->list($filters);

        return view('auditlogs.index', compact('auditLogs'));
    }
}
