<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CompaniesController extends Controller
{
    public function index(Request $request): Response
    {
        $companies = Organization::with('owner:id,name,email')
            ->with('subscriptions.plan')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('superadmin/Companies', [
            'companies' => $companies,
        ]);
    }
}
