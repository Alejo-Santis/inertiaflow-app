<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttachmentController extends Controller
{
    public function store(Request $request, Project $project, Task $task)
    {
        Gate::authorize('view', $project);

        $request->validate([
            'file' => 'required|file|max:20480', // 20 MB
        ]);

        $file        = $request->file('file');
        $storedName  = Str::uuid() . '.' . $file->getClientOriginalExtension();

        $file->storeAs('attachments', $storedName, 'public');

        $task->attachments()->create([
            'user_id'       => $request->user()->id,
            'original_name' => $file->getClientOriginalName(),
            'stored_name'   => $storedName,
            'mime_type'     => $file->getMimeType(),
            'size'          => $file->getSize(),
        ]);

        return back()->with('success', 'Archivo adjuntado.');
    }

    public function destroy(Request $request, Project $project, Task $task, TaskAttachment $attachment)
    {
        Gate::authorize('view', $project);
        abort_if($attachment->task_id !== $task->id, 404);

        // Solo el dueño o admin puede eliminar
        if ($attachment->user_id !== $request->user()->id && ! $request->user()->hasRole('admin')) {
            abort(403);
        }

        Storage::disk('public')->delete('attachments/' . $attachment->stored_name);
        $attachment->delete();

        return back()->with('success', 'Adjunto eliminado.');
    }
}
