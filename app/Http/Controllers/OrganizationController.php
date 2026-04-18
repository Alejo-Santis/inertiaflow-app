<?php

namespace App\Http\Controllers;

use App\Enums\GlobalRole;
use App\Enums\OrgMemberRole;
use App\Http\Requests\Organization\StoreOrganizationRequest;
use App\Http\Requests\Organization\UpdateOrganizationRequest;
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

        $organizations = $user->hasRole(GlobalRole::Admin->value)
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
            'orgRoles'           => OrgMemberRole::values(),
            'pendingInvitations' => $pendingInvitations,
        ]);
    }

    public function create()
    {
        return Inertia::render('Organizations/Create');
    }

    public function store(StoreOrganizationRequest $request)
    {
        $data = $request->validated();

        $data['owner_id'] = $request->user()->id;
        $data['slug']     = Str::slug($data['name']) . '-' . Str::lower(Str::random(5));
        $data['color']    = $data['color'] ?? '#6366f1';

        $org = Organization::create($data);

        OrganizationMember::create([
            'organization_id' => $org->id,
            'user_id'         => $request->user()->id,
            'role'            => OrgMemberRole::Owner,
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

    public function update(UpdateOrganizationRequest $request, Organization $organization)
    {
        $this->authorizeEdit($organization);

        $organization->update($request->validated());

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
