<?php

namespace App\Http\Middleware;

use App\Enums\GlobalRole;
use App\Enums\OrgMemberRole;
use App\Models\OrganizationMember;
use App\Models\UserNotification;
use App\Support\OrgRole;
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
    /**
     * Membresías del usuario en organizaciones con su mapa de habilidades.
     * El frontend usa esto para mostrar/ocultar controles según rol en cada org.
     */
    private function orgMemberships(Request $request): array
    {
        $user = $request->user();
        if (! $user) return [];

        return OrganizationMember::with('organization:id,uuid,name')
            ->where('user_id', $user->id)
            ->get()
            ->map(fn ($m) => [
                'organization_id'  => $m->organization_id,
                'organization_uuid' => $m->organization->uuid,
                'organization_name' => $m->organization->name,
                'role'             => $m->role,
                'abilities'        => $user->hasRole(GlobalRole::Admin->value)
                    ? array_fill_keys(array_keys(OrgRole::abilityMap(OrgMemberRole::Owner)), true)
                    : OrgRole::abilityMap($m->role),
            ])
            ->toArray();
    }

    private function recentNotifications(Request $request): array
    {
        $user = $request->user();
        if (! $user) return [];

        return UserNotification::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(6)
            ->get(['id', 'type', 'title', 'message', 'url', 'read_at', 'created_at'])
            ->map(fn ($n) => [
                'id'         => $n->id,
                'type'       => $n->type,
                'title'      => $n->title,
                'message'    => $n->message,
                'url'        => $n->url,
                'read_at'    => $n->read_at,
                'created_at' => $n->created_at->diffForHumans(),
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
                'user'           => $request->user(),
                'isAdmin'        => fn() => $request->user()?->hasRole(GlobalRole::Admin->value) ?? false,
                'roles'          => fn() => $request->user()?->getRoleNames() ?? [],
                'permissions'    => fn() => $request->user()?->getAllPermissions()->pluck('name') ?? [],
                'orgMemberships' => fn() => $this->orgMemberships($request),
            ],
            'flash' => [
                'success' => fn() => $request->session()->get('success'),
                'error'   => fn() => $request->session()->get('error'),
            ],
            'notifications'       => fn() => $this->recentNotifications($request),
            'unread_notif_count'  => fn() => $request->user()
                ? UserNotification::where('user_id', $request->user()->id)->whereNull('read_at')->count()
                : 0,
        ];
    }
}
