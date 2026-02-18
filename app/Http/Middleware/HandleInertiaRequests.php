<?php

namespace App\Http\Middleware;

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
        $user = $request->user();
        $currentOrgId = session('current_organization_id');
        $currentOrg = $currentOrgId && $user
            ? $user->organizations()->find($currentOrgId)
            : null;
        $currentRole = $currentOrg ? $user->getRoleInOrganization($currentOrg) : null;
        $canManageOrganization = $currentRole === 'owner' || $currentRole === 'admin';

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user,
                'organizations' => $user?->organizations()->with(['subscriptions' => function ($query) {
                    $query->where('status', 'active')
                        ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()))
                        ->with('plan');
                }])->get() ?? [],
                'current_organization_id' => $currentOrgId,
                'current_organization_role' => $currentRole,
                'can_manage_organization' => $canManageOrganization,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
