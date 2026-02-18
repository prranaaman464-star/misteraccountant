<?php

namespace App\Http\Controllers\Api\Onboarding;

use App\Concerns\ChecksOrganizationPermissions;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Onboarding\CreateOrganizationRequest;
use App\Models\Organization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrganizationController extends Controller
{
    use ChecksOrganizationPermissions;

    /**
     * Create a new organization.
     */
    public function store(CreateOrganizationRequest $request): JsonResponse
    {
        $user = $request->user();

        $organization = DB::transaction(function () use ($request, $user) {
            $organization = Organization::create([
                'name' => $request->name,
                'slug' => $request->slug,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'owner_id' => $user->id,
            ]);

            // Attach user as owner
            $organization->users()->attach($user->id, [
                'role' => 'owner',
                'is_active' => true,
                'joined_at' => now(),
            ]);

            return $organization;
        });

        return response()->json([
            'organization' => $organization->load('owner'),
            'message' => 'Organization created successfully. Please select a plan to continue.',
        ], 201);
    }

    /**
     * Get user's organizations.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $organizations = $user->organizations()->with('activeSubscription.plan')->get();

        return response()->json([
            'organizations' => $organizations,
        ]);
    }
}
