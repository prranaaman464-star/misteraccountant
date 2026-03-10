<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Inertia\Inertia;
use Inertia\Response;

class PackagesController extends Controller
{
    public function index(): Response
    {
        $packages = Plan::orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return Inertia::render('superadmin/Packages', [
            'packages' => $packages,
        ]);
    }
}
