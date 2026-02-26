<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Item;
use App\Models\Organization;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $organizationId = session('current_organization_id');
        $org = $organizationId
            ? Organization::find($organizationId)
            : null;

        $clientsCount = $org
            ? Client::where('organization_id', $org->id)->count()
            : 0;
        $itemsCount = Item::count();

        $recentClients = $org
            ? Client::where('organization_id', $org->id)
                ->latest()
                ->limit(5)
                ->get(['id', 'name', 'email'])
            : collect();

        return Inertia::render('Dashboard', [
            'user' => $request->user()?->only(['name']),
            'draft_invoices_count' => 0,
            'overview' => [
                'invoices' => 0,
                'customers' => $clientsCount,
                'amount' => 0,
                'quotations' => 0,
            ],
            'sales_analytics' => [
                'total_sales' => 0,
                'purchase' => 0,
                'expenses' => 0,
                'credits' => 0,
            ],
            'invoice_statistics' => [
                'invoiced' => 0,
                'received' => 0,
                'outstanding' => 0,
                'overdue' => 0,
            ],
            'total_products' => $itemsCount,
            'total_sales_count' => 0,
            'total_quotations' => 0,
            'products_change_percent' => 0,
            'sales_change_percent' => 0,
            'quotations_change_percent' => 0,
            'revenue_chart' => [
                ['day' => 'Mon', 'received' => 0, 'outstanding' => 0],
                ['day' => 'Tue', 'received' => 0, 'outstanding' => 0],
                ['day' => 'Wed', 'received' => 0, 'outstanding' => 0],
                ['day' => 'Thu', 'received' => 0, 'outstanding' => 0],
                ['day' => 'Fri', 'received' => 0, 'outstanding' => 0],
                ['day' => 'Sat', 'received' => 0, 'outstanding' => 0],
                ['day' => 'Sun', 'received' => 0, 'outstanding' => 0],
            ],
            'recent_customers' => $recentClients->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'email' => $c->email,
                'invoices_count' => 0,
                'outstanding' => 0,
            ])->toArray(),
            'recent_invoices' => [],
            'recent_transactions' => [],
            'recent_quotations' => [],
            'total_income_on_invoice' => 0,
            'total_income_change_percent' => 0,
            'top_products' => [],
        ]);
    }
}
