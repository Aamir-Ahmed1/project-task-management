<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    )
    {
        $this->middleware('auth');
    }

    public function index(Request $request): View
    {
        $filters = $request->only(['type', 'unread_only', 'per_page']);
        $notifications = $this->notificationService->getUserNotifications($request->user(), $filters);

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead(string $id): RedirectResponse
    {
        $this->notificationService->markAsRead($id);

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllAsRead(Request $request): RedirectResponse
    {
        $this->notificationService->markAllAsRead($request->user());

        return back()->with('success', 'All notifications marked as read.');
    }
}
