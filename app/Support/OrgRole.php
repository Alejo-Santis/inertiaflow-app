<?php

namespace App\Support;

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
    const EDIT_ORG           = 'edit-org';
    const DELETE_ORG         = 'delete-org';
    const MANAGE_ORG_MEMBERS = 'manage-org-members';
    const INVITE_MEMBERS     = 'invite-members';

    // Departamentos
    const CREATE_DEPT        = 'create-dept';
    const EDIT_DEPT          = 'edit-dept';
    const DELETE_DEPT        = 'delete-dept';
    const MANAGE_DEPT_MEMBERS = 'manage-dept-members';

    // Proyectos dentro de la org
    const CREATE_PROJECT     = 'create-project';
    const EDIT_PROJECT       = 'edit-project';
    const DELETE_PROJECT     = 'delete-project';
    const MANAGE_PROJECT_MEMBERS = 'manage-project-members';

    // Vistas
    const VIEW_ORG_REPORTS   = 'view-org-reports';
    const VIEW_ORG           = 'view-org';

    // ── Mapa: rol → acciones permitidas ────────────────────────────────────

    private const ROLE_ABILITIES = [
        'owner' => [
            self::EDIT_ORG, self::DELETE_ORG,
            self::MANAGE_ORG_MEMBERS, self::INVITE_MEMBERS,
            self::CREATE_DEPT, self::EDIT_DEPT, self::DELETE_DEPT, self::MANAGE_DEPT_MEMBERS,
            self::CREATE_PROJECT, self::EDIT_PROJECT, self::DELETE_PROJECT, self::MANAGE_PROJECT_MEMBERS,
            self::VIEW_ORG_REPORTS, self::VIEW_ORG,
        ],
        'admin' => [
            self::EDIT_ORG,
            self::MANAGE_ORG_MEMBERS, self::INVITE_MEMBERS,
            self::CREATE_DEPT, self::EDIT_DEPT, self::DELETE_DEPT, self::MANAGE_DEPT_MEMBERS,
            self::CREATE_PROJECT, self::EDIT_PROJECT, self::DELETE_PROJECT, self::MANAGE_PROJECT_MEMBERS,
            self::VIEW_ORG_REPORTS, self::VIEW_ORG,
        ],
        'manager' => [
            self::INVITE_MEMBERS,
            self::CREATE_DEPT, self::EDIT_DEPT, self::MANAGE_DEPT_MEMBERS,
            self::CREATE_PROJECT, self::EDIT_PROJECT, self::MANAGE_PROJECT_MEMBERS,
            self::VIEW_ORG_REPORTS, self::VIEW_ORG,
        ],
        'member' => [
            self::VIEW_ORG,
        ],
    ];

    // ── API pública ─────────────────────────────────────────────────────────

    /**
     * Verifica si un rol puede realizar una acción.
     */
    public static function can(string $role, string $action): bool
    {
        return in_array($action, self::ROLE_ABILITIES[$role] ?? [], true);
    }

    /**
     * Devuelve todas las acciones que puede hacer un rol.
     */
    public static function abilitiesFor(string $role): array
    {
        return self::ROLE_ABILITIES[$role] ?? [];
    }

    /**
     * Devuelve el mapa completo de habilidades (útil para el frontend).
     *
     * @return array<string, bool>
     */
    public static function abilityMap(string $role): array
    {
        $allActions = [
            self::EDIT_ORG,
            self::DELETE_ORG,
            self::MANAGE_ORG_MEMBERS,
            self::INVITE_MEMBERS,
            self::CREATE_DEPT,
            self::EDIT_DEPT,
            self::DELETE_DEPT,
            self::MANAGE_DEPT_MEMBERS,
            self::CREATE_PROJECT,
            self::EDIT_PROJECT,
            self::DELETE_PROJECT,
            self::MANAGE_PROJECT_MEMBERS,
            self::VIEW_ORG_REPORTS,
            self::VIEW_ORG,
        ];

        $abilities = self::ROLE_ABILITIES[$role] ?? [];

        return array_fill_keys($allActions, false)
            + array_fill_keys($abilities, true);
    }

    /**
     * Verifica si el usuario actual puede hacer la acción en una org,
     * teniendo en cuenta su rol en esa org Y su rol global de Spatie.
     *
     * Los admin globales siempre pueden todo.
     */
    public static function userCan(\App\Models\User $user, \App\Models\Organization $org, string $action): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        $member = $org->members()->where('user_id', $user->id)->first();

        if (! $member) {
            return false;
        }

        return self::can($member->role, $action);
    }
}
