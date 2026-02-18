<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class EmailController extends Controller
{
    /**
     * Show the "Enter your email" step (after plan selected).
     */
    public function show(): Response|RedirectResponse
    {
        if (! session('selected_plan_id')) {
            return redirect()->route('plans.index');
        }

        return Inertia::render('auth/EnterEmail', [
            'selectedPlanId' => session('selected_plan_id'),
        ]);
    }
}
