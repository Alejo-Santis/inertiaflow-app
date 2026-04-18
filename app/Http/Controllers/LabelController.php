<?php

namespace App\Http\Controllers;

use App\Http\Requests\Label\StoreLabelRequest;
use App\Models\Label;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class LabelController extends Controller
{
    public function store(StoreLabelRequest $request, Project $project)
    {
        Gate::authorize('view', $project);

        $validated = $request->validated();

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
