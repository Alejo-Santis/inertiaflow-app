<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('manager') || $user->hasRole('member');
    }

    public function view(User $user, Task $task): bool
    {
        return $task->project->owner_id === $user->id || $task->project->users->contains($user);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('manager');
    }

    public function update(User $user, Task $task): bool
    {
        return $task->created_by === $user->id || $user->hasRole('admin');
    }

    public function delete(User $user, Task $task): bool
    {
        return $task->created_by === $user->id || $user->hasRole('admin');
    }
}
