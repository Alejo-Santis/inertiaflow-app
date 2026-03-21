<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Models\Project;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class MeetingController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $meetings = Meeting::where('organizer_id', $user->id)
            ->orWhereHas('participants', fn($q) => $q->where('users.id', $user->id))
            ->with(['organizer:id,name', 'project:id,uuid,name,color', 'participants:id,name'])
            ->orderBy('scheduled_at')
            ->get()
            ->map(fn($m) => $this->formatMeeting($m, $user->id));

        $today     = now()->toDateString();
        $todayList = $meetings->filter(fn($m) => $m['date'] === $today)->values();
        $upcoming  = $meetings->filter(fn($m) => $m['date'] > $today)->values();
        $past      = $meetings->filter(fn($m) => $m['date'] < $today)->sortByDesc('scheduled_at')->values();

        $projects = Project::where('owner_id', $user->id)
            ->orWhereHas('users', fn($q) => $q->where('users.id', $user->id))
            ->get(['id', 'uuid', 'name', 'color']);

        $users = User::orderBy('name')->get(['id', 'name', 'uuid']);

        return Inertia::render('Meetings/Index', compact('todayList', 'upcoming', 'past', 'projects', 'users'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'            => 'required|string|max:255',
            'description'      => 'nullable|string',
            'meeting_url'      => 'nullable|url',
            'platform'         => 'nullable|string|in:zoom,meet,teams,other',
            'scheduled_at'     => 'required|date',
            'duration_minutes' => 'required|integer|min:5|max:480',
            'project_id'       => 'nullable|exists:projects,id',
            'participants'     => 'nullable|array',
            'participants.*'   => 'integer|exists:users,id',
        ]);

        $data['organizer_id'] = $request->user()->id;
        $meeting = Meeting::create($data);
        $meeting->load('project');

        $participantIds = $data['participants'] ?? [];
        if (!empty($participantIds)) {
            $meeting->participants()->sync($participantIds);
            $this->notifyParticipants($meeting, $participantIds, $request->user());
        }

        return Redirect::route('meetings.index')->with('success', 'Reunión creada.');
    }

    public function update(Request $request, Meeting $meeting)
    {
        abort_if($meeting->organizer_id !== $request->user()->id, 403);

        $data = $request->validate([
            'title'            => 'required|string|max:255',
            'description'      => 'nullable|string',
            'meeting_url'      => 'nullable|url',
            'platform'         => 'nullable|string|in:zoom,meet,teams,other',
            'scheduled_at'     => 'required|date',
            'duration_minutes' => 'required|integer|min:5|max:480',
            'project_id'       => 'nullable|exists:projects,id',
            'participants'     => 'nullable|array',
            'participants.*'   => 'integer|exists:users,id',
        ]);

        $meeting->update($data);
        $meeting->load('project');

        $prevIds    = $meeting->participants()->pluck('users.id')->toArray();
        $newIds     = $data['participants'] ?? [];
        $meeting->participants()->sync($newIds);

        $added = array_diff($newIds, $prevIds);
        if (!empty($added)) {
            $this->notifyParticipants($meeting, $added, $request->user());
        }

        return Redirect::route('meetings.index')->with('success', 'Reunión actualizada.');
    }

    public function destroy(Request $request, Meeting $meeting)
    {
        abort_if($meeting->organizer_id !== $request->user()->id, 403);
        $meeting->delete();
        return Redirect::route('meetings.index')->with('success', 'Reunión eliminada.');
    }

    private function formatMeeting(Meeting $m, int $userId): array
    {
        return [
            'uuid'             => $m->uuid,
            'title'            => $m->title,
            'description'      => $m->description,
            'meeting_url'      => $m->meeting_url,
            'platform'         => $m->platform,
            'scheduled_at'     => $m->scheduled_at->format('Y-m-d H:i'),
            'date'             => $m->scheduled_at->toDateString(),
            'time'             => $m->scheduled_at->format('H:i'),
            'duration_minutes' => $m->duration_minutes,
            'is_organizer'     => $m->organizer_id === $userId,
            'organizer'        => ['name' => $m->organizer->name],
            'project'          => $m->project ? ['uuid' => $m->project->uuid, 'name' => $m->project->name, 'color' => $m->project->color] : null,
            'participants'     => $m->participants->map(fn($p) => ['id' => $p->id, 'name' => $p->name])->toArray(),
        ];
    }

    private function notifyParticipants(Meeting $meeting, array $ids, User $organizer): void
    {
        $date = $meeting->scheduled_at->format('d/m/Y H:i');
        NotificationService::sendToMany(
            array_filter($ids, fn($id) => $id !== $organizer->id),
            'meeting_invite',
            "{$organizer->name} te invitó: \"{$meeting->title}\"",
            "📅 {$date} · {$meeting->duration_minutes} min",
            route('meetings.index')
        );
    }
}
