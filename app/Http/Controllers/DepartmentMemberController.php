<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeptMember\StoreDeptMemberRequest;
use App\Http\Requests\DeptMember\UpdateDeptMemberRequest;
use App\Models\Department;
use App\Models\DepartmentMember;
use App\Models\Organization;
use App\Models\User;
use App\Support\OrgRole;
use Illuminate\Http\Request;

class DepartmentMemberController extends Controller
{
    public function store(StoreDeptMemberRequest $request, Organization $organization, Department $department)
    {
        $this->authorizeManage($organization);
        abort_if($department->organization_id !== $organization->id, 404);

        $validated = $request->validated();

        // El usuario debe ser miembro de la organización primero
        if (! $organization->members()->where('user_id', $validated['user_id'])->exists()) {
            return back()->with('error', 'El usuario debe ser miembro de la organización primero.');
        }

        if ($department->members()->where('user_id', $validated['user_id'])->exists()) {
            return back()->with('error', 'El usuario ya pertenece a este departamento.');
        }

        DepartmentMember::create([
            'department_id' => $department->id,
            'user_id'       => $validated['user_id'],
            'role'          => $validated['role'],
        ]);

        return back()->with('success', 'Miembro agregado al departamento.');
    }

    public function update(UpdateDeptMemberRequest $request, Organization $organization, Department $department, User $user)
    {
        $this->authorizeManage($organization);
        abort_if($department->organization_id !== $organization->id, 404);

        $validated = $request->validated();

        $member = $department->members()->where('user_id', $user->id)->firstOrFail();
        $member->update(['role' => $validated['role']]);

        return back()->with('success', 'Rol en departamento actualizado.');
    }

    public function destroy(Organization $organization, Department $department, User $user)
    {
        $this->authorizeManage($organization);
        abort_if($department->organization_id !== $organization->id, 404);

        $department->members()->where('user_id', $user->id)->delete();

        return back()->with('success', 'Miembro removido del departamento.');
    }

    private function authorizeManage(Organization $organization): void
    {
        abort_unless(
            OrgRole::userCan(request()->user(), $organization, OrgRole::MANAGE_DEPT_MEMBERS),
            403, 'No tienes permisos para gestionar este departamento.'
        );
    }
}
