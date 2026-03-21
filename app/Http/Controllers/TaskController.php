<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class TaskController extends Controller
{
    public function index(Project $project, Request $request)
    {
        Gate::authorize('view', $project);

        $query = $project->tasks()->with(['creator', 'assignees']);

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }
        if ($priority = $request->query('priority')) {
            $query->where('priority', $priority);
        }

        $tasks = $query->orderByRaw("CASE status
            WHEN 'in_progress' THEN 1
            WHEN 'in_review'   THEN 2
            WHEN 'todo'        THEN 3
            WHEN 'done'        THEN 4
            WHEN 'cancelled'   THEN 5
            ELSE 6 END")
            ->orderBy('priority', 'desc')
            ->paginate(20);

        return Inertia::render('Tasks/Index', [
            'project' => $project,
            'tasks'   => $tasks,
            'filters' => $request->only(['status', 'priority']),
        ]);
    }

    public function show(Project $project, Task $task)
    {
        Gate::authorize('view', $project);

        $task->load(['creator', 'assignees', 'comments.user', 'timeLogs.user']);

        $members = $project->users()->get(['users.id', 'users.name', 'users.uuid']);

        $loggedHours = $task->timeLogs->sum('hours');

        return Inertia::render('Tasks/Show', [
            'project'      => $project,
            'task'         => $task,
            'members'      => $members,
            'logged_hours' => $loggedHours,
        ]);
    }

    public function kanban(Project $project)
    {
        Gate::authorize('view', $project);

        $tasks = $project->tasks()
            ->with(['assignees'])
            ->orderBy('priority', 'desc')
            ->get();

        return Inertia::render('Tasks/Kanban', [
            'project' => $project,
            'tasks'   => $tasks,
        ]);
    }

    public function create(Project $project)
    {
        Gate::authorize('create', Task::class);

        $members = $project->users()->get(['users.id', 'users.name', 'users.uuid']);

        return Inertia::render('Tasks/Create', [
            'project' => $project,
            'members' => $members,
        ]);
    }

    public function store(Request $request, Project $project)
    {
        Gate::authorize('create', Task::class);

        $validated = $request->validate([
            'title'           => 'required|string|max:255',
            'description'     => 'nullable|string',
            'priority'        => 'required|integer|between:1,4',
            'due_date'        => 'nullable|date|after_or_equal:today',
            'status'          => 'required|string|in:todo,in_progress,in_review,done,cancelled',
            'estimated_hours' => 'nullable|numeric|min:0',
            'assignees'       => 'nullable|array',
            'assignees.*'     => 'integer|exists:users,id',
        ]);

        $validated['project_id'] = $project->id;
        $validated['created_by'] = $request->user()->id;

        $task = Task::create($validated);

        if (! empty($validated['assignees'])) {
            $task->assignees()->sync($validated['assignees']);
        }

        return Redirect::route('projects.tasks.index', $project->uuid)
            ->with('success', 'Tarea creada con éxito.');
    }

    public function edit(Project $project, Task $task)
    {
        Gate::authorize('view', $project);

        $task->load('assignees');
        $members = $project->users()->get(['users.id', 'users.name', 'users.uuid']);

        return Inertia::render('Tasks/Edit', [
            'project' => $project,
            'task'    => $task,
            'members' => $members,
        ]);
    }

    public function update(Request $request, Project $project, Task $task)
    {
        Gate::authorize('view', $project);

        $validated = $request->validate([
            'title'           => 'required|string|max:255',
            'description'     => 'nullable|string',
            'priority'        => 'required|integer|between:1,4',
            'due_date'        => 'nullable|date',
            'status'          => 'required|string|in:todo,in_progress,in_review,done,cancelled',
            'estimated_hours' => 'nullable|numeric|min:0',
            'assignees'       => 'nullable|array',
            'assignees.*'     => 'integer|exists:users,id',
        ]);

        $task->update($validated);
        $task->assignees()->sync($validated['assignees'] ?? []);

        return Redirect::route('projects.tasks.show', [$project->uuid, $task->uuid])
            ->with('success', 'Tarea actualizada.');
    }

    public function destroy(Project $project, Task $task)
    {
        Gate::authorize('view', $project);

        $task->delete();

        return Redirect::route('projects.tasks.index', $project->uuid)
            ->with('success', 'Tarea eliminada.');
    }

    public function updateStatus(Request $request, Project $project, Task $task)
    {
        Gate::authorize('view', $project);

        $validated = $request->validate([
            'status' => 'required|string|in:todo,in_progress,in_review,done,cancelled',
        ]);

        $task->update($validated);

        return back()->with('success', 'Estado actualizado.');
    }
}
