<?php

namespace App\Http\Controllers;

use App\Enums\GlobalRole;
use App\Enums\TaskStatus;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class MyTasksController extends Controller
{
    public function index(Request $request)
    {
        $user    = $request->user();
        $isAdmin = $user->hasRole(GlobalRole::Admin->value);

        $projectTasks = Task::whereNotNull('project_id')
            ->when(! $isAdmin, fn ($q) => $q->whereHas('assignees', fn ($q) => $q->where('users.id', $user->id)))
            ->with(['project:id,uuid,name,color', 'assignees:id,name,uuid', 'creator:id,name,uuid'])
            ->when($request->input('status'), fn ($q, $v) => $q->where('status', $v))
            ->when($request->input('priority'), fn ($q, $v) => $q->where('priority', $v))
            ->orderByRaw("CASE status
                WHEN 'in_progress' THEN 1
                WHEN 'todo' THEN 2
                WHEN 'in_review' THEN 3
                WHEN 'done' THEN 4
                WHEN 'cancelled' THEN 5
                ELSE 6 END")
            ->orderBy('due_date')
            ->paginate(20)
            ->withQueryString();

        $personalTasks = Task::whereNull('project_id')
            ->where('created_by', $user->id)
            ->orderByRaw("CASE status
                WHEN 'in_progress' THEN 1
                WHEN 'todo' THEN 2
                WHEN 'in_review' THEN 3
                WHEN 'done' THEN 4
                WHEN 'cancelled' THEN 5
                ELSE 6 END")
            ->orderBy('due_date')
            ->get(['id', 'uuid', 'title', 'status', 'priority', 'due_date']);

        return Inertia::render('Tasks/Mine', [
            'projectTasks'  => $projectTasks,
            'personalTasks' => $personalTasks,
            'filters'       => $request->only(['status', 'priority']),
            'isAdmin'       => $isAdmin,
            'canCreatePersonal' => Gate::allows('createPersonal', Task::class),
        ]);
    }

    public function storePersonal(Request $request)
    {
        Gate::authorize('createPersonal', Task::class);

        $validated = $request->validate([
            'title'    => ['required', 'string', 'max:255'],
            'priority' => ['integer', 'between:1,4'],
            'due_date' => ['nullable', 'date'],
        ]);

        Task::create([
            'title'      => $validated['title'],
            'priority'   => $validated['priority'] ?? 2,
            'due_date'   => $validated['due_date'] ?? null,
            'status'     => TaskStatus::Todo->value,
            'created_by' => $request->user()->id,
            'project_id' => null,
        ]);

        return back()->with('success', 'Tarea personal creada.');
    }

    public function updatePersonalStatus(Request $request, Task $task)
    {
        Gate::authorize('update', $task);

        $validated = $request->validate([
            'status' => ['required', 'string', TaskStatus::rule()],
        ]);

        $task->update(['status' => $validated['status']]);

        return back()->with('success', 'Estado actualizado.');
    }

    public function destroyPersonal(Task $task)
    {
        Gate::authorize('delete', $task);

        $task->delete();

        return back()->with('success', 'Tarea eliminada.');
    }
}
