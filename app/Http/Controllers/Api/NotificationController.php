<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only('type', 'unread_only', 'per_page');

        $notifications = $this->notificationService->getUserNotifications($request->user(), $filters);

        return ApiResponse::paginated($notifications);
    }

    public function markAsRead(DatabaseNotification $notification): JsonResponse
    {
        if ($notification->notifiable_id !== request()->user()->id) {
            return ApiResponse::error('Forbidden.', 403);
        }

        $notification = $this->notificationService->markAsRead($notification->id);

        return ApiResponse::success($notification, 'Notification marked as read');
    }

    public function markAllAsRead(Request $request): JsonResponse
    {
        $this->notificationService->markAllAsRead($request->user());

        return ApiResponse::success(null, 'All notifications marked as read');
    }
}
