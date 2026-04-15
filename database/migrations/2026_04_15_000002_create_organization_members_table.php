<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organization_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            // Roles dentro de la organización
            // owner: dueño de la org, admin: administrador, manager: gestor de proyectos/equipos, member: miembro base
            $table->string('role')->default('member');
            $table->timestamp('joined_at')->useCurrent();

            $table->unique(['organization_id', 'user_id']);
            $table->index(['organization_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_members');
    }
};
