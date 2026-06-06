<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function __construct(
        protected TaskService $taskService
    )
    {
        $this->middleware('auth');
    }

    public function index(Request $request): View
    {
        $filters = $request->only(['status', 'priority', 'assignee_id', 'project_id', 'deadline_from', 'deadline_to', 'overdue', 'due_soon', 'search', 'per_page']);
        $tasks = $this->taskService->list($filters);

        return view('tasks.index', compact('tasks'));
    }

    public function create(): View
    {
        $projects = Project::all(['id', 'name']);
        $users = User::all(['id', 'name']);

        return view('tasks.create', compact('projects', 'users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|string|in:low,medium,high,critical',
            'status' => 'nullable|string|in:to_do,in_progress,review,completed',
            'deadline' => 'nullable|date',
            'estimated_hours' => 'nullable|numeric|min:0',
            'project_id' => 'required|exists:projects,id',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $this->taskService->create($validated);

        return redirect('/tasks')->with('success', 'Task created successfully.');
    }

    public function show(Task $task): View
    {
        $task = $this->taskService->show($task);

        return view('tasks.show', compact('task'));
    }

    public function edit(Task $task): View
    {
        $projects = Project::all(['id', 'name']);
        $users = User::all(['id', 'name']);

        return view('tasks.edit', compact('task', 'projects', 'users'));
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|string|in:low,medium,high,critical',
            'status' => 'nullable|string|in:to_do,in_progress,review,completed',
            'deadline' => 'nullable|date',
            'estimated_hours' => 'nullable|numeric|min:0',
            'actual_hours' => 'nullable|numeric|min:0',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $this->taskService->update($task, $validated);

        return redirect('/tasks')->with('success', 'Task updated successfully.');
    }

    public function destroy(Task $task): RedirectResponse
    {
        $task->delete();

        return redirect('/tasks')->with('success', 'Task deleted successfully.');
    }

    public function updateStatus(Request $request, Task $task): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|string|in:to_do,in_progress,in_review,completed,blocked',
        ]);

        $this->taskService->update($task, ['status' => $validated['status']]);

        return back()->with('success', 'Task status updated successfully.');
    }
}
