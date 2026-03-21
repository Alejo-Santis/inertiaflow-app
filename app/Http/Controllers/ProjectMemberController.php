<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProjectMemberController extends Controller
{
    public function store(Request $request, Project $project)
    {
        Gate::authorize('update', $project);

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        if ($project->users()->where('user_id', $validated['user_id'])->exists()) {
            return back()->with('error', 'El usuario ya es miembro de este proyecto.');
        }

        $project->users()->attach($validated['user_id'], [
            'role'      => 'member',
            'joined_at' => now(),
        ]);

        return back()->with('success', 'Miembro agregado al proyecto.');
    }

    public function destroy(Project $project, User $user)
    {
        Gate::authorize('update', $project);

        if ($project->owner_id === $user->id) {
            return back()->with('error', 'No puedes remover al propietario del proyecto.');
        }

        $project->users()->detach($user->id);

        return back()->with('success', 'Miembro removido del proyecto.');
    }
}
