<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfNoOrganization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        $organizationCount = $user->organizations()->count();

        if ($organizationCount === 0 && ! $request->routeIs('onboarding.*')) {
            if ($user->isSuperadmin() && session('current_organization_id')) {
                return $next($request);
            }
            if ($user->isSuperadmin()) {
                return redirect()->route('superadmin.dashboard');
            }

            return redirect()->route('onboarding.organizations.create');
        }

        if ($organizationCount > 0 && ! session('current_organization_id')) {
            $first = $user->organizations()->first();
            if ($first) {
                session(['current_organization_id' => $first->id]);
            }
        }

        return $next($request);
    }
}
