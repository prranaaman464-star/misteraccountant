<?php

namespace App\Http\Middleware;

use App\Models\Organization;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuleAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $moduleSlug): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Get organization from various sources
        $organizationId = $request->header('X-Organization-Id')
            ?? $request->query('organization_id')
            ?? $request->route('organization');

        if (! $organizationId) {
            return response()->json([
                'message' => 'Organization ID is required.',
                'requires_upgrade' => false,
            ], 400);
        }

        $organization = Organization::find($organizationId);

        if (! $organization) {
            return response()->json([
                'message' => 'Organization not found.',
                'requires_upgrade' => false,
            ], 404);
        }

        if (! $user->belongsToOrganization($organization)) {
            return response()->json([
                'message' => 'You do not have access to this organization.',
                'requires_upgrade' => false,
            ], 403);
        }

        $plan = $organization->currentPlan();

        if (! $plan) {
            return response()->json([
                'message' => 'Organization does not have an active plan.',
                'requires_upgrade' => true,
                'organization_id' => $organization->id,
            ], 402);
        }

        if (! $plan->hasModule($moduleSlug)) {
            return response()->json([
                'message' => "The '{$moduleSlug}' module is not available in your current plan.",
                'requires_upgrade' => true,
                'organization_id' => $organization->id,
                'module' => $moduleSlug,
            ], 402);
        }

        return $next($request);
    }
}
