<?php

namespace App\Support;

use App\Enums\GlobalRole;
use App\Enums\OrgMemberRole;

/**
 * Mapa de permisos por rol dentro de una organización.
 *
 * Separa lo que cada rol puede hacer DENTRO de un contexto de org,
 * independientemente del rol global de Spatie del usuario.
 *
 * Jerarquía: owner > admin > manager > member
 */
class OrgRole
{
    // ── Constantes de acción ────────────────────────────────────────────────

    // Organización
    const EDIT_ORG            = 'edit-org';
    const DELETE_ORG          = 'delete-org';
    const MANAGE_ORG_MEMBERS  = 'manage-org-members';
    const INVITE_MEMBERS      = 'invite-members';

    // Departamentos
    const CREATE_DEPT         = 'create-dept';
    const EDIT_DEPT           = 'edit-dept';
    const DELETE_DEPT         = 'delete-dept';
    const MANAGE_DEPT_MEMBERS = 'manage-dept-members';

    // Proyectos dentro de la org
    const CREATE_PROJECT          = 'create-project';
    const EDIT_PROJECT            = 'edit-project';
    const DELETE_PROJECT          = 'delete-project';
    const MANAGE_PROJECT_MEMBERS  = 'manage-project-members';

    // Vistas
    const VIEW_ORG_REPORTS = 'view-org-reports';
    const VIEW_ORG         = 'view-org';

    // ── Mapa: rol → acciones permitidas ────────────────────────────────────

    private static function roleAbilities(): array
    {
        return [
            OrgMemberRole::Owner->value => [
                self::EDIT_ORG, self::DELETE_ORG,
                self::MANAGE_ORG_MEMBERS, self::INVITE_MEMBERS,
                self::CREATE_DEPT, self::EDIT_DEPT, self::DELETE_DEPT, self::MANAGE_DEPT_MEMBERS,
                self::CREATE_PROJECT, self::EDIT_PROJECT, self::DELETE_PROJECT, self::MANAGE_PROJECT_MEMBERS,
                self::VIEW_ORG_REPORTS, self::VIEW_ORG,
            ],
            OrgMemberRole::Admin->value => [
                self::EDIT_ORG,
                self::MANAGE_ORG_MEMBERS, self::INVITE_MEMBERS,
                self::CREATE_DEPT, self::EDIT_DEPT, self::DELETE_DEPT, self::MANAGE_DEPT_MEMBERS,
                self::CREATE_PROJECT, self::EDIT_PROJECT, self::DELETE_PROJECT, self::MANAGE_PROJECT_MEMBERS,
                self::VIEW_ORG_REPORTS, self::VIEW_ORG,
            ],
            OrgMemberRole::Manager->value => [
                self::INVITE_MEMBERS,
                self::CREATE_DEPT, self::EDIT_DEPT, self::MANAGE_DEPT_MEMBERS,
                self::CREATE_PROJECT, self::EDIT_PROJECT, self::MANAGE_PROJECT_MEMBERS,
                self::VIEW_ORG_REPORTS, self::VIEW_ORG,
            ],
            OrgMemberRole::Member->value => [
                self::VIEW_ORG,
            ],
        ];
    }

    private static function allActions(): array
    {
        return [
            self::EDIT_ORG, self::DELETE_ORG,
            self::MANAGE_ORG_MEMBERS, self::INVITE_MEMBERS,
            self::CREATE_DEPT, self::EDIT_DEPT, self::DELETE_DEPT, self::MANAGE_DEPT_MEMBERS,
            self::CREATE_PROJECT, self::EDIT_PROJECT, self::DELETE_PROJECT, self::MANAGE_PROJECT_MEMBERS,
            self::VIEW_ORG_REPORTS, self::VIEW_ORG,
        ];
    }

    // ── API pública ─────────────────────────────────────────────────────────

    /** Verifica si un rol puede realizar una acción. */
    public static function can(OrgMemberRole $role, string $action): bool
    {
        return in_array($action, self::roleAbilities()[$role->value] ?? [], true);
    }

    /** Devuelve todas las acciones que puede hacer un rol. */
    public static function abilitiesFor(OrgMemberRole $role): array
    {
        return self::roleAbilities()[$role->value] ?? [];
    }

    /**
     * Devuelve el mapa completo de habilidades como bool por acción (para el frontend).
     *
     * @return array<string, bool>
     */
    public static function abilityMap(OrgMemberRole $role): array
    {
        $abilities = self::roleAbilities()[$role->value] ?? [];

        return array_merge(
            array_fill_keys(self::allActions(), false),
            array_fill_keys($abilities, true),
        );
    }

    /**
     * Verifica si el usuario puede hacer la acción en la org.
     * Los admin globales siempre pueden todo.
     */
    public static function userCan(\App\Models\User $user, \App\Models\Organization $org, string $action): bool
    {
        if ($user->hasRole(GlobalRole::Admin->value)) {
            return true;
        }

        $member = $org->members()->where('user_id', $user->id)->first();

        if (! $member) {
            return false;
        }

        return self::can($member->role, $action);
    }
}
