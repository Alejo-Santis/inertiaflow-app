<?php

namespace Database\Seeders;

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

        // Usuario de demostración
        $user = User::updateOrCreate(
            ['email' => 'demo@example.com'],
            [
                'name' => 'Demo User',
                'password' => bcrypt('password'),
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
            ]
        );
        $user->assignRole('member');
    }
}
