<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->input('q', ''));

        if (strlen($q) < 2) {
            return response()->json(['projects' => [], 'tasks' => []]);
        }

        $user       = $request->user();
        $projectIds = Project::where('owner_id', $user->id)
            ->orWhereHas('users', fn($query) => $query->where('users.id', $user->id))
            ->pluck('id');

        $projects = Project::whereIn('id', $projectIds)
            ->where('name', 'ilike', "%{$q}%")
            ->limit(5)
            ->get(['uuid', 'name', 'color', 'status']);

        $tasks = Task::whereIn('project_id', $projectIds)
            ->where('title', 'ilike', "%{$q}%")
            ->with('project:id,uuid,name,color')
            ->limit(8)
            ->get(['uuid', 'title', 'status', 'project_id']);

        return response()->json([
            'projects' => $projects,
            'tasks'    => $tasks->map(fn($t) => [
                'uuid'          => $t->uuid,
                'title'         => $t->title,
                'status'        => $t->status,
                'project_uuid'  => $t->project->uuid,
                'project_name'  => $t->project->name,
                'project_color' => $t->project->color,
            ]),
        ]);
    }
}
