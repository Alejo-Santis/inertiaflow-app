<?php

namespace App\Policies;

use App\Enums\GlobalRole;
use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(GlobalRole::Admin->value)
            || $user->hasRole(GlobalRole::Manager->value)
            || $user->hasRole(GlobalRole::Member->value);
    }

    public function view(User $user, Task $task): bool
    {
        // Tarea personal — solo el creador o admin global
        if (! $task->project_id) {
            return $task->created_by === $user->id || $user->hasRole(GlobalRole::Admin->value);
        }

        return $task->project->owner_id === $user->id || $task->project->users->contains($user);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(GlobalRole::Admin->value) || $user->hasRole(GlobalRole::Manager->value);
    }

    // Cualquier usuario autenticado puede crear sus propias tareas personales
    public function createPersonal(User $user): bool
    {
        return $user->hasRole(GlobalRole::Admin->value)
            || $user->hasRole(GlobalRole::Manager->value)
            || $user->hasRole(GlobalRole::Member->value);
    }

    public function update(User $user, Task $task): bool
    {
        return $task->created_by === $user->id || $user->hasRole(GlobalRole::Admin->value);
    }

    public function delete(User $user, Task $task): bool
    {
        return $task->created_by === $user->id || $user->hasRole(GlobalRole::Admin->value);
    }
}
