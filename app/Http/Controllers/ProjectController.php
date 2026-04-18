<?php

namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
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

        $user   = $request->user();
        $orgIds = $user->organizationMemberships()->pluck('organization_id');

        $projects = Project::with(['owner:id,name,uuid', 'organization:id,name,color,uuid'])
            ->withCount([
                'tasks',
                'tasks as done_tasks_count' => fn ($q) => $q->where('status', TaskStatus::Done->value),
                'users as members_count',
            ])
            ->where(function ($q) use ($user, $orgIds) {
                $q->where('owner_id', $user->id)
                  ->orWhereHas('users', fn ($sq) => $sq->where('users.id', $user->id))
                  ->orWhereIn('organization_id', $orgIds);
            })
            ->latest()
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
            'tasks as done_tasks_count' => fn ($q) => $q->where('status', TaskStatus::Done->value),
        ]);

        $members   = $project->users()->with('roles')->get();
        $memberIds = $members->pluck('id')->push($project->owner_id)->unique();
        $available = User::whereNotIn('id', $memberIds)
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'uuid']);

        return Inertia::render('Projects/Show', [
            'project'   => $project->load(['owner', 'organization:id,name,color,uuid', 'department:id,name,color,uuid']),
            'members'   => $members,
            'available' => $available,
        ]);
    }

    public function create()
    {
        Gate::authorize('create', Project::class);

        $user = auth()->user();

        $organizations = $user->organizations()
            ->withPivot('role')
            ->with(['departments:id,uuid,name,organization_id'])
            ->get()
            ->map(fn ($org) => [
                'id'          => $org->id,
                'uuid'        => $org->uuid,
                'name'        => $org->name,
                'color'       => $org->color ?? '#6366f1',
                'departments' => $org->departments->map(fn ($d) => [
                    'id'   => $d->id,
                    'uuid' => $d->uuid,
                    'name' => $d->name,
                ])->values(),
            ]);

        return Inertia::render('Projects/Create', [
            'organizations' => $organizations,
        ]);
    }

    public function store(StoreProjectRequest $request)
    {
        Gate::authorize('create', Project::class);

        $data = $request->validated();

        $data['owner_id'] = $request->user()->id;

        Project::create($data);

        return Redirect::route('projects.index')->with('success', 'Proyecto creado exitosamente.');
    }

    public function edit(Project $project)
    {
        Gate::authorize('view', $project);

        $user = auth()->user();

        $organizations = $user->organizations()
            ->withPivot('role')
            ->with(['departments:id,uuid,name,organization_id'])
            ->get()
            ->map(fn ($org) => [
                'id'          => $org->id,
                'uuid'        => $org->uuid,
                'name'        => $org->name,
                'color'       => $org->color ?? '#6366f1',
                'departments' => $org->departments->map(fn ($d) => [
                    'id'   => $d->id,
                    'uuid' => $d->uuid,
                    'name' => $d->name,
                ])->values(),
            ]);

        return Inertia::render('Projects/Edit', [
            'project'       => $project,
            'organizations' => $organizations,
        ]);
    }

    public function update(UpdateProjectRequest $request, Project $project)
    {
        Gate::authorize('view', $project);

        $data = $request->validated();

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
