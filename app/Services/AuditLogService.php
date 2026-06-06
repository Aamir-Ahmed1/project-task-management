<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AuditLogService
{
    public function __construct(
        protected AuditLog $auditLog
    ) {}

    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = $this->auditLog->query();

        if (! empty($filters['user_id'])) {
            $query->byUser($filters['user_id']);
        }

        if (! empty($filters['action'])) {
            $query->byAction($filters['action']);
        }

        if (! empty($filters['entity_type'])) {
            $query->byEntity(
                $filters['entity_type'],
                $filters['entity_id'] ?? null
            );
        }

        if (! empty($filters['date_from']) && ! empty($filters['date_to'])) {
            $query->byDateRange($filters['date_from'], $filters['date_to']);
        }

        $perPage = $filters['per_page'] ?? 15;

        return $query->with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}
