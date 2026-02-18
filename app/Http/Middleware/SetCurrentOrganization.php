<?php

namespace App\Http\Middleware;

use App\Models\Organization;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetCurrentOrganization
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

        // Get organization from header, query param, or route param
        $organizationId = $request->header('X-Organization-Id')
            ?? $request->query('organization_id')
            ?? $request->route('organization');

        if ($organizationId) {
            $organization = Organization::find($organizationId);

            if ($organization && $user->belongsToOrganization($organization)) {
                // Set organization in request for easy access
                $request->merge(['current_organization' => $organization]);
            }
        }

        return $next($request);
    }
}
