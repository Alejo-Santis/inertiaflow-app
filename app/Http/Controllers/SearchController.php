<?php

namespace App\Http\Controllers;

use App\Enums\GlobalRole;
use App\Models\Department;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->input('q', ''));

        if (strlen($q) < 2) {
            return response()->json([
                'projects'      => [],
                'tasks'         => [],
                'organizations' => [],
                'departments'   => [],
                'members'       => [],
            ]);
        }

        $user = $request->user();

        // ── Proyectos y tareas (lógica original) ──────────────────────────────
        $projectIds = Project::where('owner_id', $user->id)
            ->orWhereHas('users', fn($query) => $query->where('users.id', $user->id))
            ->pluck('id');

        $projects = Project::whereIn('id', $projectIds)
            ->where('name', 'ilike', "%{$q}%")
            ->limit(5)
            ->get(['uuid', 'name', 'color', 'status']);

        $tasks = Task::whereIn('project_id', $projectIds)
            ->where('title', 'ilike', "%{$q}%")
            ->with('project:id,uuid,name,color')
            ->limit(8)
            ->get(['uuid', 'title', 'status', 'project_id']);

        // ── Organizaciones ────────────────────────────────────────────────────
        $orgQuery = $user->hasRole(GlobalRole::Admin->value)
            ? Organization::query()
            : Organization::whereHas('members', fn($q2) => $q2->where('user_id', $user->id));

        $organizations = $orgQuery
            ->where('name', 'ilike', "%{$q}%")
            ->limit(5)
            ->get(['uuid', 'name', 'description']);

        $orgIds = $user->hasRole(GlobalRole::Admin->value)
            ? Organization::pluck('id')
            : OrganizationMember::where('user_id', $user->id)->pluck('organization_id');

        // ── Departamentos ─────────────────────────────────────────────────────
        $departments = Department::whereIn('organization_id', $orgIds)
            ->where('name', 'ilike', "%{$q}%")
            ->with('organization:id,uuid,name')
            ->limit(5)
            ->get(['uuid', 'name', 'color', 'organization_id']);

        // ── Miembros (por nombre de usuario) ──────────────────────────────────
        $members = OrganizationMember::whereIn('organization_id', $orgIds)
            ->whereHas('user', fn($q2) => $q2->where('name', 'ilike', "%{$q}%"))
            ->with([
                'user:id,uuid,name,email',
                'organization:id,uuid,name',
            ])
            ->limit(5)
            ->get();

        return response()->json([
            'projects'      => $projects,
            'tasks'         => $tasks->map(fn($t) => [
                'uuid'          => $t->uuid,
                'title'         => $t->title,
                'status'        => $t->status,
                'project_uuid'  => $t->project->uuid,
                'project_name'  => $t->project->name,
                'project_color' => $t->project->color,
            ]),
            'organizations' => $organizations->map(fn($o) => [
                'uuid'        => $o->uuid,
                'name'        => $o->name,
                'description' => $o->description,
            ]),
            'departments'   => $departments->map(fn($d) => [
                'uuid'      => $d->uuid,
                'name'      => $d->name,
                'color'     => $d->color,
                'org_uuid'  => $d->organization->uuid,
                'org_name'  => $d->organization->name,
            ]),
            'members'       => $members->map(fn($m) => [
                'user_name'  => $m->user->name,
                'user_email' => $m->user->email,
                'role'       => $m->role,
                'org_uuid'   => $m->organization->uuid,
                'org_name'   => $m->organization->name,
            ]),
        ]);
    }
}
