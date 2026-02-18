<?php

namespace App\Http\Controllers\Api\Onboarding;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    /**
     * Get all available plans.
     */
    public function index(Request $request): JsonResponse
    {
        $plans = Plan::where('is_active', true)
            ->orderBy('sort_order')
            ->with(['modules', 'featureLimits'])
            ->get();

        return response()->json([
            'plans' => $plans,
        ]);
    }

    /**
     * Get a specific plan.
     */
    public function show(Request $request, Plan $plan): JsonResponse
    {
        $plan->load(['modules', 'featureLimits']);

        return response()->json([
            'plan' => $plan,
        ]);
    }
}
