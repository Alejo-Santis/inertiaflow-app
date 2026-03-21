<?php

namespace App\Http\Middleware;

use App\Models\Task;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    private function recentActivity(Request $request): array
    {
        $user = $request->user();
        if (! $user) return [];

        return Task::whereHas('project', function ($q) use ($user) {
                $q->where('owner_id', $user->id)
                  ->orWhereHas('users', fn ($u) => $u->where('users.id', $user->id));
            })
            ->with('project:id,uuid,name,color')
            ->orderByDesc('updated_at')
            ->limit(8)
            ->get(['id', 'uuid', 'title', 'status', 'project_id', 'updated_at'])
            ->map(fn ($t) => [
                'id'           => $t->id,
                'uuid'         => $t->uuid,
                'title'        => $t->title,
                'status'       => $t->status,
                'updated_at'   => $t->updated_at->diffForHumans(),
                'project_uuid' => $t->project->uuid,
                'project_name' => $t->project->name,
                'project_color'=> $t->project->color,
            ])
            ->toArray();
    }

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user'    => $request->user(),
                'isAdmin' => fn() => $request->user()?->hasRole('admin') ?? false,
                'roles'   => fn() => $request->user()?->getRoleNames() ?? [],
            ],
            'flash' => [
                'success' => fn() => $request->session()->get('success'),
                'error'   => fn() => $request->session()->get('error'),
            ],
            'notifications'       => fn() => $this->recentActivity($request),
            'unread_notif_count'  => fn() => $request->user()
                ? UserNotification::where('user_id', $request->user()->id)->whereNull('read_at')->count()
                : 0,
        ];
    }
}
