<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        Gate::authorize('viewAny', Project::class);

        $projects = Project::withCount([
            'tasks',
            'tasks as done_tasks_count' => fn ($q) => $q->where('status', 'done'),
            'users as members_count',
        ])->where('owner_id', $user->id)->latest()->paginate(6);

        return Inertia::render('Dashboard/Index', [
            'user'     => $user,
            'projects' => $projects,
        ]);
    }
}
