<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CurrentOrganizationController extends Controller
{
    /**
     * Set the current organization in session.
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'organization_id' => ['required', 'integer', 'exists:organizations,id'],
        ]);

        $organization = Organization::findOrFail($request->organization_id);
        $user = $request->user();

        if (! $user?->isSuperadmin() && ! $user?->belongsToOrganization($organization)) {
            abort(403);
        }

        session(['current_organization_id' => $organization->id]);

        return redirect()->back();
    }
}
