<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BatchExpiryReportController extends Controller
{
    public function index(Request $request): \Inertia\Response
    {
        return Inertia::render('inventory/BatchExpiryReport');
    }
}
