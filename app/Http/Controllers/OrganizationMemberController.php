<?php

namespace App\Http\Controllers;

use App\Enums\OrgMemberRole;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\User;
use App\Support\OrgRole;
use Illuminate\Http\Request;

class OrganizationMemberController extends Controller
{
    public function store(Request $request, Organization $organization)
    {
        $this->authorizeManage($organization);

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'role'    => ['required', OrgMemberRole::rule()],
        ]);

        if ($organization->members()->where('user_id', $validated['user_id'])->exists()) {
            return back()->with('error', 'El usuario ya es miembro de esta organización.');
        }

        OrganizationMember::create([
            'organization_id' => $organization->id,
            'user_id'         => $validated['user_id'],
            'role'            => $validated['role'],
        ]);

        return back()->with('success', 'Miembro agregado a la organización.');
    }

    public function update(Request $request, Organization $organization, User $user)
    {
        $this->authorizeManage($organization);

        $validated = $request->validate([
            'role' => ['required', OrgMemberRole::rule()],
        ]);

        $member = $organization->members()->where('user_id', $user->id)->firstOrFail();

        if ($member->role === OrgMemberRole::Owner) {
            return back()->with('error', 'No se puede cambiar el rol del owner.');
        }

        $member->update(['role' => $validated['role']]);

        return back()->with('success', 'Rol actualizado.');
    }

    public function destroy(Organization $organization, User $user)
    {
        $this->authorizeManage($organization);

        $member = $organization->members()->where('user_id', $user->id)->firstOrFail();

        if ($member->role === OrgMemberRole::Owner) {
            return back()->with('error', 'No puedes remover al owner de la organización.');
        }

        $member->delete();

        return back()->with('success', 'Miembro removido de la organización.');
    }

    private function authorizeManage(Organization $organization): void
    {
        abort_unless(
            OrgRole::userCan(request()->user(), $organization, OrgRole::MANAGE_ORG_MEMBERS),
            403, 'Solo owner o admin pueden gestionar miembros.'
        );
    }
}
