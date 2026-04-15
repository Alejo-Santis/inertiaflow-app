<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\DepartmentMember;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Permisos primero (los roles necesitan existir)
        $this->call(PermissionSeeder::class);

        // Usuario inicial
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => bcrypt('password'),
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
            ]
        );

        // Roles y permisos de Spatie
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'manager']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'member']);

        $admin->assignRole('admin');

        // Usuarios de demostración
        $manager = User::updateOrCreate(
            ['email' => 'manager@example.com'],
            [
                'name' => 'Demo Manager',
                'password' => bcrypt('password'),
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
            ]
        );
        $manager->assignRole('manager');

        $techLead = User::updateOrCreate(
            ['email' => 'techlead@example.com'],
            [
                'name' => 'Tech Lead',
                'password' => bcrypt('password'),
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
            ]
        );
        $techLead->assignRole('member');

        $dev1 = User::updateOrCreate(
            ['email' => 'dev1@example.com'],
            [
                'name' => 'Developer One',
                'password' => bcrypt('password'),
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
            ]
        );
        $dev1->assignRole('member');

        $dev2 = User::updateOrCreate(
            ['email' => 'dev2@example.com'],
            [
                'name' => 'Developer Two',
                'password' => bcrypt('password'),
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
            ]
        );
        $dev2->assignRole('member');

        // Organización de demostración
        $org = Organization::updateOrCreate(
            ['slug' => 'acme-tech'],
            [
                'owner_id'    => $admin->id,
                'name'        => 'ACME Tech',
                'description' => 'Organización de demostración',
                'uuid'        => (string) \Illuminate\Support\Str::uuid(),
            ]
        );

        // Miembros de la organización
        OrganizationMember::updateOrCreate(
            ['organization_id' => $org->id, 'user_id' => $admin->id],
            ['role' => OrganizationMember::ROLE_OWNER]
        );
        OrganizationMember::updateOrCreate(
            ['organization_id' => $org->id, 'user_id' => $manager->id],
            ['role' => OrganizationMember::ROLE_MANAGER]
        );
        OrganizationMember::updateOrCreate(
            ['organization_id' => $org->id, 'user_id' => $techLead->id],
            ['role' => OrganizationMember::ROLE_MEMBER]
        );
        OrganizationMember::updateOrCreate(
            ['organization_id' => $org->id, 'user_id' => $dev1->id],
            ['role' => OrganizationMember::ROLE_MEMBER]
        );
        OrganizationMember::updateOrCreate(
            ['organization_id' => $org->id, 'user_id' => $dev2->id],
            ['role' => OrganizationMember::ROLE_MEMBER]
        );

        // Departamento de Desarrollo
        $dept = Department::updateOrCreate(
            ['organization_id' => $org->id, 'name' => 'Desarrollo'],
            [
                'lead_id'     => $manager->id,
                'description' => 'Equipo de desarrollo de software',
                'color'       => '#6366f1',
                'uuid'        => (string) \Illuminate\Support\Str::uuid(),
            ]
        );

        // Jerarquía del departamento
        DepartmentMember::updateOrCreate(
            ['department_id' => $dept->id, 'user_id' => $manager->id],
            ['role' => DepartmentMember::ROLE_TEAM_LEAD]
        );
        DepartmentMember::updateOrCreate(
            ['department_id' => $dept->id, 'user_id' => $techLead->id],
            ['role' => DepartmentMember::ROLE_TECH_LEAD]
        );
        DepartmentMember::updateOrCreate(
            ['department_id' => $dept->id, 'user_id' => $dev1->id],
            ['role' => DepartmentMember::ROLE_SENIOR]
        );
        DepartmentMember::updateOrCreate(
            ['department_id' => $dept->id, 'user_id' => $dev2->id],
            ['role' => DepartmentMember::ROLE_MEMBER]
        );
    }
}
