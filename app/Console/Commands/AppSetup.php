<?php

namespace App\Console\Commands;

use App\Models\Department;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AppSetup extends Command
{
    protected $signature   = 'app:setup';
    protected $description = 'Configura InertiaFlow por primera vez: roles, admin y organización inicial.';

    public function handle(): int
    {
        $this->newLine();
        $this->line('  <fg=bright-white;bg=indigo>  InertiaFlow — Configuración inicial  </>');
        $this->newLine();

        // ── 1. Roles y permisos ──────────────────────────────────────────
        $this->info('[ 1/4 ] Creando roles y permisos...');
        $this->callSilent('db:seed', ['--class' => PermissionSeeder::class]);
        $this->line('        <fg=green>✓</> Roles: admin, manager, member');
        $this->newLine();

        // ── 2. Usuario administrador ─────────────────────────────────────
        $this->info('[ 2/4 ] Crear usuario administrador');

        $name  = $this->ask('        Nombre completo');
        $email = $this->ask('        Email');

        while (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->line('        <fg=red>Email inválido.</>');
            $email = $this->ask('        Email');
        }

        $password = $this->secret('        Contraseña (mínimo 8 caracteres)');

        while (strlen($password) < 8) {
            $this->line('        <fg=red>La contraseña debe tener al menos 8 caracteres.</>');
            $password = $this->secret('        Contraseña');
        }

        $admin = User::updateOrCreate(
            ['email' => $email],
            [
                'name'     => $name,
                'password' => Hash::make($password),
                'uuid'     => (string) Str::uuid(),
            ]
        );

        // Asignar rol admin (quitar roles anteriores si los tuviera)
        $admin->syncRoles('admin');

        $this->line("        <fg=green>✓</> Administrador <fg=yellow>{$name}</> ({$email}) creado.");
        $this->newLine();

        // ── 3. Organización inicial ──────────────────────────────────────
        $this->info('[ 3/4 ] Crear organización inicial');

        $orgName = $this->ask('        Nombre de la empresa / organización');
        $orgDesc = $this->ask('        Descripción (opcional, Enter para omitir)', '');

        $org = Organization::updateOrCreate(
            ['slug' => Str::slug($orgName) . '-' . Str::lower(Str::random(4))],
            [
                'owner_id'    => $admin->id,
                'name'        => $orgName,
                'description' => $orgDesc ?: null,
                'uuid'        => (string) Str::uuid(),
            ]
        );

        OrganizationMember::updateOrCreate(
            ['organization_id' => $org->id, 'user_id' => $admin->id],
            ['role' => OrganizationMember::ROLE_OWNER]
        );

        $this->line("        <fg=green>✓</> Organización <fg=yellow>{$orgName}</> creada.");

        // Departamento inicial opcional
        if ($this->confirm('        ¿Crear un primer departamento?', true)) {
            $deptName  = $this->ask('        Nombre del departamento', 'Desarrollo');
            $deptColor = $this->choice(
                '        Color del departamento',
                ['Indigo (#6366f1)', 'Violet (#8b5cf6)', 'Emerald (#10b981)', 'Sky (#0ea5e9)', 'Rose (#f43f5e)'],
                0
            );

            $colorMap = [
                'Indigo (#6366f1)'   => '#6366f1',
                'Violet (#8b5cf6)'   => '#8b5cf6',
                'Emerald (#10b981)'  => '#10b981',
                'Sky (#0ea5e9)'      => '#0ea5e9',
                'Rose (#f43f5e)'     => '#f43f5e',
            ];

            Department::create([
                'organization_id' => $org->id,
                'lead_id'         => $admin->id,
                'name'            => $deptName,
                'color'           => $colorMap[$deptColor] ?? '#6366f1',
                'uuid'            => (string) Str::uuid(),
            ]);

            $this->line("        <fg=green>✓</> Departamento <fg=yellow>{$deptName}</> creado.");
        }

        $this->newLine();

        // ── 4. Resumen final ─────────────────────────────────────────────
        $this->info('[ 4/4 ] Resumen');
        $this->table(
            ['Elemento', 'Valor'],
            [
                ['URL de la app',      config('app.url')],
                ['Administrador',      $name],
                ['Email de acceso',    $email],
                ['Organización',       $orgName],
                ['Worker de emails',   './start-worker.sh'],
            ]
        );

        $this->newLine();
        $this->line('  <fg=green>✓ Configuración completada.</>');
        $this->newLine();
        $this->line('  Próximos pasos:');
        $this->line('  <fg=yellow>1.</> Configurar SMTP en <fg=cyan>.env</> (MAIL_HOST, MAIL_USERNAME, etc.)');
        $this->line('  <fg=yellow>2.</> Ejecutar <fg=cyan>./start-worker.sh</> para procesar emails en cola');
        $this->line('  <fg=yellow>3.</> Iniciar sesión en <fg=cyan>' . config('app.url') . '</> con ' . $email);
        $this->newLine();

        return self::SUCCESS;
    }
}
