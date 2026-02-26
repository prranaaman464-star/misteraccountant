<?php

namespace App\Http\Middleware;

use App\Models\Organization;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BindCurrentOrganization
{
    /**
     * Bind the current organization to the container for global scopes.
     * Resolves org from: session (web), X-Organization-Id header, organization_id query, or route param.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        $organizationId = session('current_organization_id')
            ?? $request->header('X-Organization-Id')
            ?? $request->query('organization_id')
            ?? $request->route('organization');

        if (! $organizationId) {
            return $next($request);
        }

        $organization = $organizationId instanceof Organization
            ? $organizationId
            : Organization::find($organizationId);

        if (! $organization) {
            return $next($request);
        }

        if (! $user->isSuperadmin() && ! $user->belongsToOrganization($organization)) {
            return $next($request);
        }

        app()->instance('current.organization', $organization);
        $request->merge(['current_organization' => $organization]);

        return $next($request);
    }
}
