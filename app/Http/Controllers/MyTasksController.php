<?php

namespace App\Http\Controllers;

use App\Enums\GlobalRole;
use App\Models\Task;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MyTasksController extends Controller
{
    public function index(Request $request)
    {
        $user    = $request->user();
        $isAdmin = $user->hasRole(GlobalRole::Admin->value);

        $tasks = Task::when(! $isAdmin, fn ($q) => $q->whereHas('assignees', fn ($q) => $q->where('users.id', $user->id)))
            ->with(['project:id,uuid,name,color', 'assignees:id,name,uuid', 'creator:id,name,uuid'])
            ->when($request->input('status'), fn($q, $v) => $q->where('status', $v))
            ->when($request->input('priority'), fn($q, $v) => $q->where('priority', $v))
            ->orderByRaw("CASE status
                WHEN 'in_progress' THEN 1
                WHEN 'todo' THEN 2
                WHEN 'review' THEN 3
                WHEN 'done' THEN 4
                WHEN 'cancelled' THEN 5
                ELSE 6 END")
            ->orderBy('due_date')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Tasks/Mine', [
            'tasks'   => $tasks,
            'filters' => $request->only(['status', 'priority']),
            'isAdmin' => $isAdmin,
        ]);
    }
}
