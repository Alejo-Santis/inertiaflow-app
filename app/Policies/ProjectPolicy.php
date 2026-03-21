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
        return $project->owner_id === $user->id || $project->users->contains($user);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('manager');
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
