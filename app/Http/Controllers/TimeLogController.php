<?php

namespace App\Http\Controllers;

use App\Enums\GlobalRole;
use App\Http\Requests\TimeLog\StoreTimeLogRequest;
use App\Models\Project;
use App\Models\Task;
use App\Models\TimeLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TimeLogController extends Controller
{
    public function store(StoreTimeLogRequest $request, Project $project, Task $task)
    {
        Gate::authorize('view', $project);

        $validated = $request->validated();

        $task->timeLogs()->create([
            'user_id'     => $request->user()->id,
            'hours'       => $validated['hours'],
            'description' => $validated['description'] ?? null,
            'logged_date' => $validated['logged_date'],
        ]);

        return back()->with('success', 'Tiempo registrado.');
    }

    public function destroy(Project $project, Task $task, TimeLog $timeLog)
    {
        Gate::authorize('view', $project);

        if ($timeLog->user_id !== auth()->id() && ! auth()->user()->hasRole(GlobalRole::Admin->value)) {
            abort(403);
        }

        $timeLog->delete();

        return back()->with('success', 'Registro eliminado.');
    }
}
