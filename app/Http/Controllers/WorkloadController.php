<?php

namespace App\Http\Controllers;

use App\Enums\GlobalRole;
use App\Models\Organization;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class WorkloadController extends Controller
{
    public function index(Request $request)
    {
        $user   = $request->user();
        $orgIds = $user->organizationMemberships()->pluck('organization_id');

        $projectIds = Project::where(function ($q) use ($user, $orgIds) {
            $q->where('owner_id', $user->id)
              ->orWhereHas('users', fn ($sq) => $sq->where('users.id', $user->id))
              ->orWhereIn('organization_id', $orgIds);
        })->pluck('id');

        // Filter by project if requested
        $filterProjectId = $request->query('project_id');
        if ($filterProjectId) {
            $project = Project::where('uuid', $filterProjectId)->firstOrFail();
            Gate::authorize('view', $project);
            $projectIds = collect([$project->id]);
        }

        // Filter by org if requested
        $filterOrgId = $request->query('org_id');
        if ($filterOrgId) {
            $org = Organization::where('uuid', $filterOrgId)->firstOrFail();
            $projectIds = Project::whereIn('organization_id', [$org->id])
                ->whereIn('id', $projectIds)
                ->pluck('id');
        }

        // Build workload: users with open task counts
        $workload = User::whereHas('assignedTasks', fn ($q) =>
            $q->whereIn('tasks.project_id', $projectIds)
              ->whereNotIn('tasks.status', ['done', 'cancelled'])
        )
        ->withCount([
            'assignedTasks as open_tasks' => fn ($q) =>
                $q->whereIn('tasks.project_id', $projectIds)
                  ->whereNotIn('tasks.status', ['done', 'cancelled']),
            'assignedTasks as in_progress_tasks' => fn ($q) =>
                $q->whereIn('tasks.project_id', $projectIds)
                  ->where('tasks.status', 'in_progress'),
            'assignedTasks as in_review_tasks' => fn ($q) =>
                $q->whereIn('tasks.project_id', $projectIds)
                  ->where('tasks.status', 'in_review'),
            'assignedTasks as todo_tasks' => fn ($q) =>
                $q->whereIn('tasks.project_id', $projectIds)
                  ->where('tasks.status', 'todo'),
        ])
        ->orderByDesc('open_tasks')
        ->get(['id', 'name', 'email', 'avatar_path', 'uuid']);

        // Append avatar_url
        $workload = $workload->map(fn ($u) => array_merge($u->toArray(), [
            'avatar_url' => $u->avatar_url,
        ]));

        $organizations = $user->hasRole(GlobalRole::Admin->value)
            ? Organization::orderBy('name')->get(['id', 'uuid', 'name', 'color'])
            : $user->organizations()->orderBy('name')->get(['organizations.id', 'organizations.uuid', 'organizations.name', 'organizations.color']);

        $projects = Project::whereIn('id', Project::whereIn('id', $projectIds)->pluck('id'))
            ->orderBy('name')
            ->get(['id', 'uuid', 'name']);

        return Inertia::render('Workload/Index', [
            'workload'      => $workload,
            'organizations' => $organizations,
            'projects'      => $projects,
            'filters'       => [
                'project_id' => $filterProjectId,
                'org_id'     => $filterOrgId,
            ],
        ]);
    }
}
