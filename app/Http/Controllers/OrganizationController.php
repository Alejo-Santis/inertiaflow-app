<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\OrganizationInvitation;
use App\Models\OrganizationMember;
use App\Models\User;
use App\Support\OrgRole;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class OrganizationController extends Controller
{
    public function index()
    {
        $user = request()->user();

        // Admin ve todas; el resto solo las suyas
        $organizations = $user->hasRole('admin')
            ? Organization::with('owner')->withCount(['members', 'departments', 'projects'])->get()
            : $user->organizations()->with('owner')->withCount(['members', 'departments', 'projects'])->get();

        return Inertia::render('Organizations/Index', [
            'organizations' => $organizations,
        ]);
    }

    public function show(Organization $organization)
    {
        $this->authorizeAccess($organization);

        $organization->load([
            'owner',
            'departments' => fn ($q) => $q->withCount('members')->with('lead'),
            'members.user',
        ]);

        $memberUserIds = $organization->members->pluck('user_id');
        $available = User::whereNotIn('id', $memberUserIds)
            ->orderBy('name')
            ->get(['id', 'uuid', 'name', 'email']);

        $pendingInvitations = OrganizationInvitation::with('inviter')
            ->where('organization_id', $organization->id)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($inv) => [
                'id'         => $inv->id,
                'email'      => $inv->email,
                'role'       => $inv->role,
                'expires_at' => $inv->expires_at->format('d/m/Y'),
                'inviter'    => ['name' => $inv->inviter->name],
            ]);

        return Inertia::render('Organizations/Show', [
            'organization'       => $organization,
            'available'          => $available,
            'orgRoles'           => OrganizationMember::roles(),
            'pendingInvitations' => $pendingInvitations,
        ]);
    }

    public function create()
    {
        return Inertia::render('Organizations/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'color'       => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $data['owner_id'] = $request->user()->id;
        $data['slug']     = Str::slug($data['name']) . '-' . Str::lower(Str::random(5));
        $data['color']    = $data['color'] ?? '#6366f1';

        $org = Organization::create($data);

        // El creador queda como owner automáticamente
        OrganizationMember::create([
            'organization_id' => $org->id,
            'user_id'         => $request->user()->id,
            'role'            => OrganizationMember::ROLE_OWNER,
        ]);

        return redirect()->route('organizations.show', $org->uuid)
            ->with('success', 'Organización creada exitosamente.');
    }

    public function edit(Organization $organization)
    {
        $this->authorizeEdit($organization);

        return Inertia::render('Organizations/Edit', [
            'organization' => $organization,
        ]);
    }

    public function update(Request $request, Organization $organization)
    {
        $this->authorizeEdit($organization);

        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'color'       => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $organization->update($data);

        return redirect()->route('organizations.show', $organization->uuid)
            ->with('success', 'Organización actualizada.');
    }

    public function destroy(Organization $organization)
    {
        abort_unless(
            OrgRole::userCan(request()->user(), $organization, OrgRole::DELETE_ORG),
            403,
            'Solo el owner puede eliminar la organización.'
        );

        $organization->delete();

        return redirect()->route('organizations.index')
            ->with('success', 'Organización eliminada.');
    }

    private function authorizeAccess(Organization $organization): void
    {
        abort_unless(
            OrgRole::userCan(request()->user(), $organization, OrgRole::VIEW_ORG),
            403,
            'No tienes acceso a esta organización.'
        );
    }

    private function authorizeEdit(Organization $organization): void
    {
        abort_unless(
            OrgRole::userCan(request()->user(), $organization, OrgRole::EDIT_ORG),
            403,
            'No tienes permisos para editar esta organización.'
        );
    }
}
