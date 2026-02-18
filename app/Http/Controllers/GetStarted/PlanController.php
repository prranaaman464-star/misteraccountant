<?php

namespace App\Http\Controllers\GetStarted;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PlanController extends Controller
{
    /**
     * Public plan selection (before login/signup). SaasyKit-style: plan first.
     */
    public function index(): Response
    {
        $plans = Plan::where('is_active', true)
            ->orderBy('sort_order')
            ->with(['modules', 'featureLimits'])
            ->get();

        return Inertia::render('get-started/SelectPlan', [
            'plans' => $plans,
        ]);
    }

    /**
     * Store selected plan in session and redirect to enter email.
     */
    public function select(Request $request): RedirectResponse
    {
        $request->validate([
            'plan_id' => ['required', 'exists:plans,id'],
        ]);

        session(['selected_plan_id' => $request->plan_id]);

        return redirect()->route('auth.email');
    }
}
