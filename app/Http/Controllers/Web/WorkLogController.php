<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\WorkLog;
use App\Services\WorkLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkLogController extends Controller
{
    public function __construct(
        protected WorkLogService $workLogService
    )
    {
        $this->middleware('auth');
    }

    public function index(Task $task): View
    {
        $workLogs = $this->workLogService->listByTask($task->id);

        return view('worklogs.index', compact('task', 'workLogs'));
    }

    public function create(Task $task): View
    {
        return view('worklogs.create', compact('task'));
    }

    public function store(Request $request, Task $task): RedirectResponse
    {
        $validated = $request->validate([
            'description' => 'required|string',
            'hours_worked' => 'required|numeric|min:0',
            'attachment' => 'nullable|string',
            'logged_at' => 'nullable|date',
        ]);

        $validated['task_id'] = $task->id;
        $validated['user_id'] = auth()->id();

        $this->workLogService->create($validated);

        return back()->with('success', 'Work log created successfully.');
    }

    public function addReply(Request $request, WorkLog $workLog): RedirectResponse
    {
        $validated = $request->validate([
            'reply' => 'required|string',
        ]);

        $this->workLogService->addReply($workLog->id, auth()->id(), $validated['reply']);

        return back()->with('success', 'Reply added successfully.');
    }

    public function showReplies(WorkLog $workLog): View
    {
        $replies = $this->workLogService->getReplies($workLog->id);

        return view('worklogs.replies', compact('workLog', 'replies'));
    }
}
