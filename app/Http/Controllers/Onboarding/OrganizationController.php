<?php

namespace App\Http\Controllers\Onboarding;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Onboarding\CreateOrganizationRequest;
use App\Models\Organization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class OrganizationController extends Controller
{
    /**
     * Show the create organization form.
     */
    public function create(): Response
    {
        return Inertia::render('onboarding/CreateOrganization');
    }

    /**
     * Store a newly created organization.
     */
    public function store(CreateOrganizationRequest $request): RedirectResponse
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

            $organization->users()->attach($user->id, [
                'role' => 'owner',
                'is_active' => true,
                'joined_at' => now(),
            ]);

            return $organization;
        });

        session(['current_organization_id' => $organization->id]);

        return redirect()->route('onboarding.plans.index');
    }
}
