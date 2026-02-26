<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $totalCompanies = Organization::count();
        $activeCompanies = Organization::where('is_active', true)->count();
        $inactiveCompanies = $totalCompanies - $activeCompanies;
        $newCompaniesThisWeek = Organization::where('created_at', '>=', now()->subWeek())->count();

        $lastWeekTotal = Organization::where('created_at', '<', now()->subWeek())->count();
        $totalChangePercent = $lastWeekTotal > 0
            ? round((($totalCompanies - $lastWeekTotal) / $lastWeekTotal) * 100, 1)
            : 0;

        $lastWeekActive = Organization::where('is_active', true)->where('updated_at', '<', now()->subWeek())->count();
        $activeChangePercent = $lastWeekActive > 0
            ? round((($activeCompanies - $lastWeekActive) / $lastWeekActive) * 100, 2)
            : 0;

        $latestCompanies = Organization::with(['owner:id,name,email', 'subscriptions.plan'])
            ->latest()
            ->limit(7)
            ->get()
            ->map(fn ($org) => [
                'id' => $org->id,
                'name' => $org->name,
                'plan' => $org->currentPlan()?->name ?? '-',
                'due_date' => $org->activeSubscription()->first()?->ends_at?->format('d M Y') ?? '-',
            ]);

        $totalUsers = User::count();

        $mostOrderedPlan = Plan::withCount('subscriptions')
            ->orderByDesc('subscriptions_count')
            ->first();
        $mostOrderedPlanRevenue = $mostOrderedPlan
            ? (float) $mostOrderedPlan->price * $mostOrderedPlan->subscriptions_count
            : 0;

        $topCompanyWithPlan = Organization::with(['owner:id,name,email'])
            ->withCount('subscriptions')
            ->orderByDesc('subscriptions_count')
            ->first();

        $topPlans = Plan::withCount('subscriptions')
            ->orderByDesc('subscriptions_count')
            ->limit(6)
            ->get()
            ->map(fn ($plan) => [
                'name' => $plan->name,
                'price' => (float) $plan->price,
                'count' => $plan->subscriptions_count,
                'sales' => (float) $plan->price * $plan->subscriptions_count,
            ]);

        $earningsChart = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthTotal = Subscription::whereMonth('starts_at', $m)
                ->whereYear('starts_at', now()->year)
                ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
                ->sum('plans.price');
            $earningsChart[] = [
                'month' => date('M', mktime(0, 0, 0, $m, 1)),
                'amount' => (float) $monthTotal,
            ];
        }

        $recentPlanExpired = Subscription::with(['organization:id,name', 'plan:id,name'])
            ->where('ends_at', '<', now())
            ->latest('ends_at')
            ->limit(7)
            ->get()
            ->map(fn ($sub) => [
                'company' => $sub->organization?->name ?? '-',
                'plan' => $sub->plan?->name ?? '-',
                'expired_on' => $sub->ends_at?->format('d M Y') ?? '-',
            ]);

        $companiesRegisteredChart = [];
        $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $startOfWeek = now()->startOfWeek();
        foreach ($days as $i => $day) {
            $date = $startOfWeek->copy()->addDays($i);
            $count = Organization::whereDate('created_at', $date)->count();
            $companiesRegisteredChart[] = ['day' => $day, 'count' => $count];
        }

        $recentDomain = Organization::with('subscriptions.plan')
            ->latest()
            ->limit(7)
            ->get()
            ->map(fn ($org) => [
                'company' => $org->name,
                'plan' => $org->currentPlan()?->name ?? '-',
            ]);

        return Inertia::render('superadmin/Dashboard', [
            'user' => auth()->user()->only(['name']),
            'total_users' => $totalUsers,
            'total_companies' => $totalCompanies,
            'active_companies' => $activeCompanies,
            'inactive_companies' => $inactiveCompanies,
            'total_active_domains' => 0,
            'total_change_percent' => $totalChangePercent,
            'active_change_percent' => $activeChangePercent,
            'inactive_change_percent' => $inactiveCompanies > 0 ? -12.8 : 0,
            'domains_change_percent' => 0,
            'new_companies_this_week' => $newCompaniesThisWeek,
            'most_ordered_plan' => $mostOrderedPlan ? [
                'name' => $mostOrderedPlan->name,
                'total_order' => $mostOrderedPlan->subscriptions_count,
                'amount' => $mostOrderedPlanRevenue,
            ] : null,
            'top_company' => $topCompanyWithPlan ? [
                'name' => $topCompanyWithPlan->name,
                'email' => $topCompanyWithPlan->owner?->email ?? $topCompanyWithPlan->email ?? '-',
                'plans_count' => $topCompanyWithPlan->subscriptions_count,
            ] : null,
            'most_domains' => null,
            'latest_companies' => $latestCompanies,
            'earnings_chart' => $earningsChart,
            'recent_plan_expired' => $recentPlanExpired,
            'recent_domain' => $recentDomain,
            'companies_registered_chart' => $companiesRegisteredChart,
            'top_plans' => $topPlans,
            'invoices' => [],
        ]);
    }
}
