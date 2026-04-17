<?php

namespace App\Http\Controllers;

use App\Mail\CommentAdded;
use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;

class CommentController extends Controller
{
    public function store(Request $request, Project $project, Task $task)
    {
        Gate::authorize('view', $project);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $comment = $task->comments()->create([
            'user_id' => $request->user()->id,
            'body'    => $validated['body'],
        ]);

        $comment->load('user');
        $task->load('project');

        // Parse @mentions
        preg_match_all('/@([\w\.\-]+)/', $validated['body'], $matches);
        $mentionedUserIds = [];
        if (!empty($matches[1])) {
            $mentionedUsers = User::whereIn('name', $matches[1])
                ->where('id', '!=', $request->user()->id)
                ->get();

            foreach ($mentionedUsers as $mentioned) {
                $mentionedUserIds[] = $mentioned->id;
                NotificationService::send(
                    $mentioned->id,
                    'mentioned',
                    "{$request->user()->name} te mencionó en \"{$task->title}\"",
                    substr($validated['body'], 0, 120),
                    route('projects.tasks.show', [$project->uuid, $task->uuid])
                );
                Mail::to($mentioned->email)->queue(new CommentAdded($task, $comment, $mentioned, 'mentioned'));
            }
        }

        // Notify task assignees about the new comment (except commenter and already-notified mentions)
        $assignees = $task->assignees()
            ->where('users.id', '!=', $request->user()->id)
            ->whereNotIn('users.id', $mentionedUserIds)
            ->get();

        $assigneeIds = $assignees->pluck('id')->toArray();

        if (!empty($assigneeIds)) {
            NotificationService::sendToMany(
                $assigneeIds,
                'comment_added',
                "{$request->user()->name} comentó en \"{$task->title}\"",
                substr($validated['body'], 0, 120),
                route('projects.tasks.show', [$project->uuid, $task->uuid])
            );
            foreach ($assignees as $assignee) {
                Mail::to($assignee->email)->queue(new CommentAdded($task, $comment, $assignee, 'assignee'));
            }
        }

        return back()->with('success', 'Comentario agregado.');
    }

    public function destroy(Project $project, Task $task, Comment $comment)
    {
        if ($comment->user_id !== Auth::id() && ! FacadesAuth::user()->hasRole('admin')) {
            abort(403, 'No puedes eliminar este comentario.');
        }

        $comment->delete();

        return back()->with('success', 'Comentario eliminado.');
    }
}
