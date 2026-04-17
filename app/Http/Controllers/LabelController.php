<?php

namespace App\Http\Controllers;

use App\Models\Label;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class LabelController extends Controller
{
    public function store(Request $request, Project $project)
    {
        Gate::authorize('view', $project);

        $validated = $request->validate([
            'name'  => 'required|string|max:50',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $label = $project->labels()->firstOrCreate(
            ['name' => $validated['name']],
            ['color' => $validated['color']]
        );

        return response()->json($label);
    }

    public function destroy(Project $project, Label $label)
    {
        Gate::authorize('view', $project);

        abort_if($label->project_id !== $project->id, 404);

        $label->delete();

        return response()->json(['ok' => true]);
    }
}
