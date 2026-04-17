<?php

namespace App\Http\Controllers;

use App\Mail\TaskAssigned;
use App\Mail\TaskStatusChanged;
use App\Models\Project;
use App\Models\Task;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
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

        $task->load(['creator', 'assignees', 'comments.user', 'timeLogs.user', 'attachments.user', 'labels']);

        $members = $project->users()->get(['users.id', 'users.name', 'users.uuid']);

        $loggedHours = $task->timeLogs->sum('hours');

        // Append public URL and formatted size to each attachment
        $attachments = $task->attachments->map(fn($a) => array_merge($a->toArray(), [
            'url'            => $a->url,
            'formatted_size' => $a->formatted_size,
        ]));

        $labels = $project->labels()->orderBy('name')->get();

        return Inertia::render('Tasks/Show', [
            'project'      => $project,
            'task'         => $task,
            'members'      => $members,
            'logged_hours' => $loggedHours,
            'attachments'  => $attachments,
            'labels'       => $labels,
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
        $labels  = $project->labels()->orderBy('name')->get();

        return Inertia::render('Tasks/Create', [
            'project' => $project,
            'members' => $members,
            'labels'  => $labels,
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
            'meeting_url'     => 'nullable|url',
            'assignees'       => 'nullable|array',
            'assignees.*'     => 'integer|exists:users,id',
            'label_ids'       => 'nullable|array',
            'label_ids.*'     => 'integer|exists:labels,id',
        ]);

        $validated['project_id'] = $project->id;
        $validated['created_by'] = $request->user()->id;

        $task = Task::create($validated);
        $task->load('project');

        if (! empty($validated['assignees'])) {
            $task->assignees()->sync($validated['assignees']);
            $this->notifyAssignees($task, $validated['assignees'], $request->user());
        }

        $task->labels()->sync($validated['label_ids'] ?? []);

        return Redirect::route('projects.tasks.index', $project->uuid)
            ->with('success', 'Tarea creada con éxito.');
    }

    public function edit(Project $project, Task $task)
    {
        Gate::authorize('view', $project);

        $task->load(['assignees', 'labels']);
        $members = $project->users()->get(['users.id', 'users.name', 'users.uuid']);
        $labels  = $project->labels()->orderBy('name')->get();

        return Inertia::render('Tasks/Edit', [
            'project' => $project,
            'task'    => $task,
            'members' => $members,
            'labels'  => $labels,
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
            'meeting_url'     => 'nullable|url',
            'assignees'       => 'nullable|array',
            'assignees.*'     => 'integer|exists:users,id',
            'label_ids'       => 'nullable|array',
            'label_ids.*'     => 'integer|exists:labels,id',
        ]);

        $previousAssigneeIds = $task->assignees()->pluck('users.id')->toArray();
        $task->update($validated);
        $task->load('project');
        $newAssigneeIds = $validated['assignees'] ?? [];
        $task->assignees()->sync($newAssigneeIds);
        $task->labels()->sync($validated['label_ids'] ?? []);

        // Notify only newly added assignees
        $addedIds = array_diff($newAssigneeIds, $previousAssigneeIds);
        if (!empty($addedIds)) {
            $this->notifyAssignees($task, $addedIds, $request->user());
        }

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

        $previousStatus = $task->status;
        $task->update($validated);

        if ($previousStatus !== $validated['status']) {
            $task->load('project');
            $assignees = $task->assignees()
                ->where('users.id', '!=', $request->user()->id)
                ->get();

            foreach ($assignees as $assignee) {
                NotificationService::send(
                    $assignee->id,
                    'status_changed',
                    "{$request->user()->name} cambió el estado de \"{$task->title}\"",
                    "{$previousStatus} → {$validated['status']}",
                    route('projects.tasks.show', [$project->uuid, $task->uuid])
                );
                Mail::to($assignee->email)->queue(
                    new TaskStatusChanged($task, $assignee, $previousStatus, $request->user())
                );
            }
        }

        return back()->with('success', 'Estado actualizado.');
    }

    private function notifyAssignees(Task $task, array $assigneeIds, $assignedBy): void
    {
        $assignees = \App\Models\User::whereIn('id', $assigneeIds)
            ->where('id', '!=', $assignedBy->id)
            ->get();

        foreach ($assignees as $assignee) {
            // DB notification
            NotificationService::send(
                $assignee->id,
                'task_assigned',
                "{$assignedBy->name} te asignó: \"{$task->title}\"",
                "Proyecto: {$task->project->name}",
                route('projects.tasks.show', [$task->project->uuid, $task->uuid])
            );
            // Email (queued)
            Mail::to($assignee->email)->queue(new TaskAssigned($task, $assignee, $assignedBy));
        }
    }

    public function export(Project $project)
    {
        Gate::authorize('view', $project);

        $tasks = $project->tasks()
            ->with(['creator:id,name', 'assignees:id,name'])
            ->orderBy('status')
            ->orderBy('priority')
            ->get();

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . str()->slug($project->name) . '-tareas.csv"',
        ];

        $callback = function () use ($tasks, $project) {
            $out = fopen('php://output', 'w');
            // BOM for Excel UTF-8
            fputs($out, "\xEF\xBB\xBF");
            fputcsv($out, ['ID', 'Título', 'Estado', 'Prioridad', 'Asignados', 'Creado por', 'Fecha vencimiento', 'Horas estimadas', 'Creado el']);
            foreach ($tasks as $task) {
                fputcsv($out, [
                    $task->uuid,
                    $task->title,
                    $task->status,
                    $task->priority,
                    $task->assignees->pluck('name')->join(', '),
                    $task->creator?->name ?? '—',
                    $task->due_date ?? '—',
                    $task->estimated_hours ?? '—',
                    $task->created_at->format('d/m/Y'),
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}
