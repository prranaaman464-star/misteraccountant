<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Inertia\Inertia;
use Inertia\Response;

class SubscriptionsController extends Controller
{
    public function index(): Response
    {
        $subscriptions = Subscription::with(['organization:id,name,slug', 'plan:id,name,slug,price,billing_cycle'])
            ->latest()
            ->paginate(15);

        return Inertia::render('superadmin/Subscriptions', [
            'subscriptions' => $subscriptions,
        ]);
    }
}
