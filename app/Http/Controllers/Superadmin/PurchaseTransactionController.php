<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class PurchaseTransactionController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('superadmin/PurchaseTransaction', [
            'transactions' => [],
        ]);
    }
}
