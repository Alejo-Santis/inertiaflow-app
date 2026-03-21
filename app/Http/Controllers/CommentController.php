<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    public function store(Request $request, Project $project, Task $task)
    {
        Gate::authorize('view', $project);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $task->comments()->create([
            'user_id' => $request->user()->id,
            'body'    => $validated['body'],
        ]);

        return back()->with('success', 'Comentario agregado.');
    }

    public function destroy(Project $project, Task $task, Comment $comment)
    {
        if ($comment->user_id !== auth()->id() && ! auth()->user()->hasRole('admin')) {
            abort(403, 'No puedes eliminar este comentario.');
        }

        $comment->delete();

        return back()->with('success', 'Comentario eliminado.');
    }
}
