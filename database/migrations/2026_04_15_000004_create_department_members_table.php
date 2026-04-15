<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('department_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            // Jerarquía dentro del departamento:
            // team_lead: líder del equipo/departamento
            // tech_lead: referente técnico
            // senior: desarrollador senior
            // member: miembro base del equipo
            $table->string('role')->default('member');
            $table->timestamp('joined_at')->useCurrent();

            $table->unique(['department_id', 'user_id']);
            $table->index(['department_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('department_members');
    }
};
