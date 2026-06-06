<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only('user_id', 'action', 'entity_type', 'entity_id', 'date_from', 'date_to', 'per_page');

        $logs = $this->auditLogService->list($filters);

        return ApiResponse::paginated($logs);
    }
}
