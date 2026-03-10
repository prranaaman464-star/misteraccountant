<?php

namespace App\Http\Controllers\Purchase;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VendorsLeadsController extends Controller
{
    public function index(Request $request): \Inertia\Response
    {
        return Inertia::render('purchase/VendorsLeads');
    }
}
