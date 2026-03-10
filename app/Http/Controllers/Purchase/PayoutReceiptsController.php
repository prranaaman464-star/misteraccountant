<?php

namespace App\Http\Controllers\Purchase;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PayoutReceiptsController extends Controller
{
    public function index(Request $request): \Inertia\Response
    {
        return Inertia::render('purchase/PayoutReceipts');
    }
}
