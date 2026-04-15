<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        Gate::authorize('viewAny', Project::class);

        $orgIds = $user->organizationMemberships()->pluck('organization_id');

        $projects = Project::with(['owner:id,name,uuid', 'organization:id,name,color,uuid'])
            ->withCount([
                'tasks',
                'tasks as done_tasks_count' => fn ($q) => $q->where('status', 'done'),
                'users as members_count',
            ])
            ->where(function ($q) use ($user, $orgIds) {
                $q->where('owner_id', $user->id)
                  ->orWhereHas('users', fn ($sq) => $sq->where('users.id', $user->id))
                  ->orWhereIn('organization_id', $orgIds);
            })
            ->latest()
            ->paginate(6);

        $organizations = $user->organizations()
            ->withCount(['members', 'departments', 'projects'])
            ->withPivot('role')
            ->get()
            ->map(fn ($org) => [
                'uuid'              => $org->uuid,
                'name'              => $org->name,
                'color'             => $org->color ?? '#6366f1',
                'role'              => $org->pivot->role,
                'members_count'     => $org->members_count,
                'departments_count' => $org->departments_count,
                'projects_count'    => $org->projects_count,
            ])
            ->values();

        return Inertia::render('Dashboard/Index', [
            'user'          => $user,
            'projects'      => $projects,
            'organizations' => $organizations,
        ]);
    }
}
