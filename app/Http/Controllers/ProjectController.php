<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Project::class);

        $projects = Project::with('owner')
            ->withCount([
                'tasks',
                'tasks as done_tasks_count' => fn ($q) => $q->where('status', 'done'),
                'users as members_count',
            ])
            ->paginate(10);

        return Inertia::render('Projects/Index', [
            'projects' => $projects,
        ]);
    }

    public function show(Project $project)
    {
        Gate::authorize('view', $project);

        $project->loadCount([
            'tasks',
            'tasks as done_tasks_count' => fn ($q) => $q->where('status', 'done'),
        ]);

        $members   = $project->users()->with('roles')->get();
        $memberIds = $members->pluck('id')->push($project->owner_id)->unique();
        $available = User::whereNotIn('id', $memberIds)
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'uuid']);

        return Inertia::render('Projects/Show', [
            'project'   => $project->load('owner'),
            'members'   => $members,
            'available' => $available,
        ]);
    }

    public function create()
    {
        Gate::authorize('create', Project::class);

        return Inertia::render('Projects/Create');
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Project::class);

        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'status'      => 'required|string|in:active,on_hold,completed,cancelled',
            'color'       => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $data['owner_id'] = $request->user()->id;

        Project::create($data);

        return Redirect::route('projects.index')->with('success', 'Proyecto creado exitosamente.');
    }

    public function edit(Project $project)
    {
        Gate::authorize('view', $project);

        return Inertia::render('Projects/Edit', [
            'project' => $project,
        ]);
    }

    public function update(Request $request, Project $project)
    {
        Gate::authorize('view', $project);

        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'status'      => 'required|string|in:active,on_hold,completed,cancelled',
            'color'       => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $project->update($data);

        return Redirect::route('projects.show', $project->uuid)
            ->with('success', 'Proyecto actualizado.');
    }

    public function destroy(Project $project)
    {
        Gate::authorize('view', $project);

        $project->delete();

        return Redirect::route('projects.index')
            ->with('success', 'Proyecto eliminado.');
    }
}
