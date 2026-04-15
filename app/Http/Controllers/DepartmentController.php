<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\DepartmentMember;
use App\Models\Organization;
use App\Models\User;
use App\Support\OrgRole;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DepartmentController extends Controller
{
    public function show(Organization $organization, Department $department)
    {
        $this->authorizeOrgAccess($organization);

        abort_if($department->organization_id !== $organization->id, 404);

        $department->load([
            'lead',
            'members.user',
            'projects' => fn ($q) => $q->withCount('tasks'),
        ]);

        // Miembros de la org que aún no están en el departamento
        $deptUserIds  = $department->members->pluck('user_id');
        $orgUserIds   = $organization->members()->pluck('user_id');
        $available    = User::whereIn('id', $orgUserIds)
            ->whereNotIn('id', $deptUserIds)
            ->orderBy('name')
            ->get(['id', 'uuid', 'name', 'email']);

        return Inertia::render('Organizations/Department', [
            'organization' => $organization->only(['id', 'uuid', 'name']),
            'department'   => $department,
            'available'    => $available,
            'deptRoles'    => DepartmentMember::roles(),
        ]);
    }

    public function store(Request $request, Organization $organization)
    {
        $this->authorizeManage($organization);

        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'color'       => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'lead_id'     => 'nullable|exists:users,id',
        ]);

        $data['organization_id'] = $organization->id;
        $data['color']           = $data['color'] ?? '#6366f1';

        $department = Department::create($data);

        // Si se asigna líder, agregarlo al departamento automáticamente
        if (!empty($data['lead_id'])) {
            DepartmentMember::firstOrCreate(
                ['department_id' => $department->id, 'user_id' => $data['lead_id']],
                ['role' => DepartmentMember::ROLE_TEAM_LEAD]
            );
        }

        return redirect()->route('organizations.show', $organization->uuid)
            ->with('success', 'Departamento creado exitosamente.');
    }

    public function update(Request $request, Organization $organization, Department $department)
    {
        $this->authorizeManage($organization);
        abort_if($department->organization_id !== $organization->id, 404);

        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'color'       => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'lead_id'     => 'nullable|exists:users,id',
        ]);

        $department->update($data);

        return back()->with('success', 'Departamento actualizado.');
    }

    public function destroy(Organization $organization, Department $department)
    {
        $this->authorizeManage($organization);
        abort_if($department->organization_id !== $organization->id, 404);

        $department->delete();

        return redirect()->route('organizations.show', $organization->uuid)
            ->with('success', 'Departamento eliminado.');
    }

    private function authorizeOrgAccess(Organization $organization): void
    {
        abort_unless(
            OrgRole::userCan(request()->user(), $organization, OrgRole::VIEW_ORG),
            403, 'No tienes acceso a esta organización.'
        );
    }

    private function authorizeManage(Organization $organization): void
    {
        abort_unless(
            OrgRole::userCan(request()->user(), $organization, OrgRole::CREATE_DEPT),
            403, 'No tienes permisos para gestionar departamentos.'
        );
    }
}
