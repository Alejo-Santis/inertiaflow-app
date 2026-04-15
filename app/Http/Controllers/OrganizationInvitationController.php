<?php

namespace App\Http\Controllers;

use App\Mail\OrganizationInvitationMail;
use App\Models\Organization;
use App\Models\OrganizationInvitation;
use App\Models\OrganizationMember;
use App\Models\User;
use App\Support\OrgRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class OrganizationInvitationController extends Controller
{
    /**
     * Enviar invitación a un email (owner o admin de la org)
     */
    public function store(Request $request, Organization $organization)
    {
        $this->authorizeManage($organization);

        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'role'  => 'required|in:' . implode(',', OrganizationMember::roles()),
        ]);

        // No invitar si ya es miembro
        $existingUser = User::where('email', $validated['email'])->first();
        if ($existingUser && $organization->members()->where('user_id', $existingUser->id)->exists()) {
            return back()->with('error', 'Ese usuario ya es miembro de la organización.');
        }

        // Evitar invitaciones duplicadas pendientes
        $existing = OrganizationInvitation::where('organization_id', $organization->id)
            ->where('email', $validated['email'])
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->first();

        if ($existing) {
            return back()->with('error', 'Ya existe una invitación pendiente para ese email.');
        }

        // Limpiar invitaciones expiradas anteriores del mismo email
        OrganizationInvitation::where('organization_id', $organization->id)
            ->where('email', $validated['email'])
            ->whereNull('accepted_at')
            ->where('expires_at', '<=', now())
            ->delete();

        $invitation = OrganizationInvitation::create([
            'organization_id' => $organization->id,
            'invited_by'      => $request->user()->id,
            'email'           => $validated['email'],
            'role'            => $validated['role'],
            'token'           => OrganizationInvitation::generateToken(),
            'expires_at'      => now()->addDays(7),
        ]);

        Mail::to($invitation->email)->send(
            new OrganizationInvitationMail($invitation->load('organization', 'inviter'))
        );

        return back()->with('success', "Invitación enviada a {$invitation->email}.");
    }

    /**
     * Página pública de aceptar invitación (accesible sin login)
     */
    public function show(string $token)
    {
        $invitation = OrganizationInvitation::with(['organization', 'inviter'])
            ->where('token', $token)
            ->firstOrFail();

        // Invitación ya aceptada
        if ($invitation->isAccepted()) {
            return Inertia::render('Invitations/Accept', [
                'status'     => 'already_accepted',
                'invitation' => $this->invitationData($invitation),
            ]);
        }

        // Invitación expirada
        if ($invitation->isExpired()) {
            return Inertia::render('Invitations/Accept', [
                'status'     => 'expired',
                'invitation' => $this->invitationData($invitation),
            ]);
        }

        // Invitación válida
        return Inertia::render('Invitations/Accept', [
            'status'     => 'pending',
            'invitation' => $this->invitationData($invitation),
            'token'      => $token,
        ]);
    }

    /**
     * Aceptar invitación (usuario autenticado)
     */
    public function accept(Request $request, string $token)
    {
        $invitation = OrganizationInvitation::with(['organization'])
            ->where('token', $token)
            ->firstOrFail();

        $user = $request->user();

        if ($invitation->isAccepted()) {
            return redirect()->route('organizations.show', $invitation->organization->uuid)
                ->with('error', 'Esta invitación ya fue aceptada.');
        }

        if ($invitation->isExpired()) {
            return redirect()->route('invitations.show', $token)
                ->with('error', 'Esta invitación ha expirado.');
        }

        // Verificar que el email del usuario coincide con el invitado
        if (strtolower($user->email) !== strtolower($invitation->email)) {
            return back()->with('error', "Esta invitación fue enviada a {$invitation->email}. Inicia sesión con ese correo para aceptarla.");
        }

        // Si ya es miembro, marcar como aceptada y redirigir
        if ($invitation->organization->members()->where('user_id', $user->id)->exists()) {
            $invitation->update(['accepted_at' => now()]);
            return redirect()->route('organizations.show', $invitation->organization->uuid)
                ->with('success', 'Ya eres miembro de esta organización.');
        }

        // Agregar a la organización
        OrganizationMember::create([
            'organization_id' => $invitation->organization_id,
            'user_id'         => $user->id,
            'role'            => $invitation->role,
        ]);

        // Garantizar que el usuario tenga al menos el rol Spatie "member"
        // para poder acceder a proyectos, tareas, etc. No se baja de rango
        // si ya es admin o manager.
        if ($user->getRoleNames()->isEmpty()) {
            $user->assignRole('member');
        }

        $invitation->update(['accepted_at' => now()]);

        return redirect()->route('organizations.show', $invitation->organization->uuid)
            ->with('success', "¡Bienvenido/a a {$invitation->organization->name}!");
    }

    /**
     * Cancelar (eliminar) invitación pendiente
     */
    public function destroy(Organization $organization, OrganizationInvitation $invitation)
    {
        $this->authorizeManage($organization);

        abort_if($invitation->organization_id !== $organization->id, 404);

        $invitation->delete();

        return back()->with('success', 'Invitación cancelada.');
    }

    private function invitationData(OrganizationInvitation $invitation): array
    {
        $roleLabels = [
            'owner'   => 'Owner',
            'admin'   => 'Admin',
            'manager' => 'Manager',
            'member'  => 'Member',
        ];

        return [
            'email'        => $invitation->email,
            'role'         => $invitation->role,
            'role_label'   => $roleLabels[$invitation->role] ?? $invitation->role,
            'expires_at'   => $invitation->expires_at?->format('d/m/Y'),
            'accepted_at'  => $invitation->accepted_at?->format('d/m/Y H:i'),
            'organization' => [
                'name'        => $invitation->organization->name,
                'uuid'        => $invitation->organization->uuid,
                'description' => $invitation->organization->description,
            ],
            'inviter' => [
                'name' => $invitation->inviter->name,
            ],
        ];
    }

    private function authorizeManage(Organization $organization): void
    {
        abort_unless(
            OrgRole::userCan(request()->user(), $organization, OrgRole::INVITE_MEMBERS),
            403, 'No tienes permisos para gestionar invitaciones.'
        );
    }
}
