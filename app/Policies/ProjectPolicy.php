<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('manager') || $user->hasRole('member');
    }

    public function view(User $user, Project $project): bool
    {
        // Dueño o miembro directo del proyecto
        if ($project->owner_id === $user->id || $project->users->contains($user)) {
            return true;
        }

        // Miembro de la organización a la que pertenece el proyecto
        if ($project->organization_id) {
            return $project->organization
                ->members()
                ->where('user_id', $user->id)
                ->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        // Cualquier usuario autenticado puede crear proyectos
        // (admin, manager y member del sistema)
        return $user->hasRole('admin') || $user->hasRole('manager') || $user->hasRole('member');
    }

    public function update(User $user, Project $project): bool
    {
        return $project->owner_id === $user->id || $user->hasRole('admin');
    }

    public function delete(User $user, Project $project): bool
    {
        return $project->owner_id === $user->id || $user->hasRole('admin');
    }
}
