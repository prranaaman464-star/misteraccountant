<?php

namespace App\Http\Controllers\Sale;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DeliveryChallansController extends Controller
{
    public function index(Request $request): \Inertia\Response
    {
        return Inertia::render('sale/DeliveryChallans');
    }
}
