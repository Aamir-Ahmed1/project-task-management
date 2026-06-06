<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use App\Services\ProjectService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function __construct(
        protected ProjectService $projectService
    )
    {
        $this->middleware('auth');
    }

    public function index(Request $request): View
    {
        $filters = $request->only(['status', 'manager_id', 'date_from', 'date_to', 'search', 'per_page']);
        $projects = $this->projectService->list($filters);

        return view('projects.index', compact('projects'));
    }

    public function create(): View
    {
        $projectManagers = User::role('project-manager')->get(['id', 'name']);

        return view('projects.create', compact('projectManagers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'nullable|string|in:planning,active,completed,archived',
            'project_manager_id' => 'nullable|exists:users,id',
        ]);

        $this->projectService->create($validated);

        return redirect('/projects')->with('success', 'Project created successfully.');
    }

    public function show(Project $project): View
    {
        $project = $this->projectService->show($project);

        return view('projects.show', compact('project'));
    }

    public function edit(Project $project): View
    {
        $projectManagers = User::role('project-manager')->get(['id', 'name']);

        return view('projects.edit', compact('project', 'projectManagers'));
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'nullable|string|in:planning,active,completed,archived',
            'project_manager_id' => 'nullable|exists:users,id',
        ]);

        $this->projectService->update($project, $validated);

        return redirect('/projects')->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $this->projectService->archive($project);

        return redirect('/projects')->with('success', 'Project archived successfully.');
    }
}
