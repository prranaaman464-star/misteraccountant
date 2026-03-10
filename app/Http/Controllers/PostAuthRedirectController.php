<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Subscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PostAuthRedirectController extends Controller
{
    /**
     * After login or register: if user selected a plan before auth, create org + subscription then go to dashboard.
     */
    public function __invoke(): RedirectResponse
    {
        $user = auth()->user();

        if ($user?->isSuperadmin()) {
            return redirect()->route('superadmin.dashboard');
        }
        $planId = session('selected_plan_id');

        if ($planId && $user) {
            $organizationCount = $user->organizations()->count();

            if ($organizationCount === 0) {
                DB::transaction(function () use ($user, $planId) {
                    $organization = Organization::create([
                        'name' => $user->name."'s Organization",
                        'slug' => Str::slug($user->name).'-'.Str::random(5),
                        'owner_id' => $user->id,
                    ]);

                    $organization->users()->attach($user->id, [
                        'role' => 'owner',
                        'is_active' => true,
                        'joined_at' => now(),
                    ]);

                    Subscription::create([
                        'organization_id' => $organization->id,
                        'plan_id' => $planId,
                        'status' => 'active',
                        'starts_at' => now(),
                        'ends_at' => now()->addMonth(),
                    ]);

                    session(['current_organization_id' => $organization->id]);
                });
            }

            session()->forget('selected_plan_id');
        }

        return redirect()->route('dashboard');
    }
}
