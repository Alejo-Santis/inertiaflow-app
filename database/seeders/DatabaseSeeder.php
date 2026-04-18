<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\DepartmentMember;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Ramsey\Uuid\Uuid;

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
            ['email' => 'admin@inertiaflow.com'],
            [
                'name' => 'Admin',
                'password' => bcrypt('Superadmin123.'),
                'uuid' => (string) Uuid::uuid4()->toString(),
            ]
        );

        // Roles y permisos de Spatie
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'manager']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'member']);

        $admin->assignRole('admin');

        // Usuarios de demostración
        $manager = User::updateOrCreate(
            ['email' => 'alvaro.dimas@inertiaflow.com'],
            [
                'name' => 'Alvaro Dimas',
                'password' => bcrypt('Password123.'),
                'uuid' => (string) Uuid::uuid4()->toString(),
            ]
        );
        $manager->assignRole('manager');

        $techLead = User::updateOrCreate(
            ['email' => 'techlead@inertiaflow.com'],
            [
                'name' => 'Alejandro Santis',
                'password' => bcrypt('Password123.'),
                'uuid' => (string) Uuid::uuid4()->toString(),
            ]
        );
        $techLead->assignRole('member');

        $dev1 = User::updateOrCreate(
            ['email' => 'alejandro.santis@inertiaflow.com'],
            [
                'name' => 'Alejandro Santis',
                'password' => bcrypt('Password123.'),
                'uuid' => (string) Uuid::uuid4()->toString(),
            ]
        );
        $dev1->assignRole('member');

        $dev2 = User::updateOrCreate(
            ['email' => 'ivan.hernandez@inertiaflow.com'],
            [
                'name' => 'Ivan Hernandez',
                'password' => bcrypt('Password123.'),
                'uuid' => (string) Uuid::uuid4()->toString(),
            ]
        );
        $dev2->assignRole('member');

        // Organización de demostración
        $org = Organization::updateOrCreate(
            ['slug' => 'nextpyme-colombia-sas'],
            [
                'owner_id'    => $admin->id,
                'nit'         => '901249232',
                'dv'          => '0',
                'name'        => 'Nextpyme Colombia S.A.S.',
                'description' => 'Empresa dedicada a brindar soluciones tecnológicas para pequeñas y medianas empresas en Colombia.',
                'uuid'        => (string) Uuid::uuid4()->toString(),
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
                'uuid'        => (string) Uuid::uuid4()->toString(),
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
