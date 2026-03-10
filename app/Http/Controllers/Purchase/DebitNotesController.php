<?php

namespace App\Http\Controllers\Purchase;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DebitNotesController extends Controller
{
    public function index(Request $request): \Inertia\Response
    {
        return Inertia::render('purchase/DebitNotes');
    }
}
