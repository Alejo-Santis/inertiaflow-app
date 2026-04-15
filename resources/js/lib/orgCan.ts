/**
 * Helper de permisos por organización para el frontend.
 *
 * Usa los datos de `auth.orgMemberships` compartidos desde el servidor
 * para determinar qué puede hacer el usuario en cada organización,
 * sin hacer requests adicionales.
 *
 * Uso:
 *   import { useOrgAbilities } from '$lib/orgCan';
 *   const can = useOrgAbilities(orgUuid);
 *   if (can('create-dept')) { ... }
 */

import { usePage } from '@inertiajs/svelte';

// Espejo de las constantes de OrgRole.php
export const OrgActions = {
  EDIT_ORG:             'edit-org',
  DELETE_ORG:           'delete-org',
  MANAGE_ORG_MEMBERS:   'manage-org-members',
  INVITE_MEMBERS:       'invite-members',
  CREATE_DEPT:          'create-dept',
  EDIT_DEPT:            'edit-dept',
  DELETE_DEPT:          'delete-dept',
  MANAGE_DEPT_MEMBERS:  'manage-dept-members',
  CREATE_PROJECT:       'create-project',
  EDIT_PROJECT:         'edit-project',
  DELETE_PROJECT:       'delete-project',
  MANAGE_PROJECT_MEMBERS: 'manage-project-members',
  VIEW_ORG_REPORTS:     'view-org-reports',
  VIEW_ORG:             'view-org',
} as const;

export type OrgAction = typeof OrgActions[keyof typeof OrgActions];

export interface OrgMembership {
  organization_id:   number;
  organization_uuid: string;
  organization_name: string;
  role:              string;
  abilities:         Record<OrgAction, boolean>;
}

/**
 * Devuelve una función `can(action)` para la organización indicada.
 * Si el usuario no es miembro de esa org, todas las acciones retornan false.
 *
 * @param orgUuid UUID de la organización
 */
export function useOrgAbilities(orgUuid: string): (action: OrgAction) => boolean {
  const page = usePage();
  const memberships: OrgMembership[] = (page.props.auth as any)?.orgMemberships ?? [];
  const membership = memberships.find(m => m.organization_uuid === orgUuid);

  if (!membership) {
    return () => false;
  }

  return (action: OrgAction) => membership.abilities[action] ?? false;
}

/**
 * Devuelve todas las membresías del usuario.
 */
export function useOrgMemberships(): OrgMembership[] {
  const page = usePage();
  return (page.props.auth as any)?.orgMemberships ?? [];
}

/**
 * Devuelve el rol del usuario en una org específica, o null si no es miembro.
 */
export function useOrgRole(orgUuid: string): string | null {
  const memberships = useOrgMemberships();
  return memberships.find(m => m.organization_uuid === orgUuid)?.role ?? null;
}

/**
 * Devuelve true si el usuario tiene el permiso global de Spatie indicado.
 */
export function useHasPermission(permission: string): boolean {
  const page = usePage();
  const permissions: string[] = (page.props.auth as any)?.permissions ?? [];
  return permissions.includes(permission);
}
