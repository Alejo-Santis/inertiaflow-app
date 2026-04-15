<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Permisos del sistema agrupados por módulo.
     * Estos son permisos globales (Spatie) para acciones que no dependen
     * del contexto de una organización específica.
     */
    private const PERMISSIONS = [
        // ── Organizaciones ─────────────────────────────────────────────────
        'orgs.view'    => 'Ver organizaciones',
        'orgs.create'  => 'Crear organizaciones',

        // ── Proyectos ──────────────────────────────────────────────────────
        'projects.view'   => 'Ver proyectos',
        'projects.create' => 'Crear proyectos',
        'projects.edit'   => 'Editar proyectos',
        'projects.delete' => 'Eliminar proyectos',
        'projects.manage-members' => 'Gestionar miembros de proyectos',
        'projects.export' => 'Exportar proyectos',

        // ── Tareas ─────────────────────────────────────────────────────────
        'tasks.view'    => 'Ver tareas',
        'tasks.create'  => 'Crear tareas',
        'tasks.edit'    => 'Editar tareas',
        'tasks.delete'  => 'Eliminar tareas',
        'tasks.assign'  => 'Asignar tareas a otros usuarios',
        'tasks.log-time' => 'Registrar tiempo en tareas',

        // ── Reuniones ──────────────────────────────────────────────────────
        'meetings.view'   => 'Ver reuniones',
        'meetings.create' => 'Crear reuniones',
        'meetings.edit'   => 'Editar reuniones',
        'meetings.delete' => 'Eliminar reuniones',

        // ── Reportes ───────────────────────────────────────────────────────
        'reports.analytics' => 'Ver analíticas y estadísticas',

        // ── Administración (solo admin) ────────────────────────────────────
        'admin.users'     => 'Gestionar usuarios del sistema',
        'admin.audit-log' => 'Ver log de auditoría',
    ];

    /**
     * Permisos asignados a cada rol global.
     */
    private const ROLE_PERMISSIONS = [
        'admin' => [
            // Admin tiene todos los permisos vía Gate::before(), pero los
            // definimos explícitamente para que sean visibles en la UI.
            'orgs.view', 'orgs.create',
            'projects.view', 'projects.create', 'projects.edit', 'projects.delete',
            'projects.manage-members', 'projects.export',
            'tasks.view', 'tasks.create', 'tasks.edit', 'tasks.delete',
            'tasks.assign', 'tasks.log-time',
            'meetings.view', 'meetings.create', 'meetings.edit', 'meetings.delete',
            'reports.analytics',
            'admin.users', 'admin.audit-log',
        ],
        'manager' => [
            'orgs.view', 'orgs.create',
            'projects.view', 'projects.create', 'projects.edit', 'projects.delete',
            'projects.manage-members', 'projects.export',
            'tasks.view', 'tasks.create', 'tasks.edit', 'tasks.delete',
            'tasks.assign', 'tasks.log-time',
            'meetings.view', 'meetings.create', 'meetings.edit', 'meetings.delete',
            'reports.analytics',
        ],
        'member' => [
            'orgs.view',
            'projects.view',
            'tasks.view', 'tasks.create', 'tasks.edit', 'tasks.log-time',
            'meetings.view',
        ],
    ];

    public function run(): void
    {
        // Crear permisos
        foreach (self::PERMISSIONS as $name => $description) {
            Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                ['name' => $name, 'guard_name' => 'web']
            );
        }

        // Asignar permisos a roles
        foreach (self::ROLE_PERMISSIONS as $roleName => $permissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($permissions);
        }
    }
}
