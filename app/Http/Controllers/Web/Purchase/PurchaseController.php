<?php

namespace App\Http\Controllers\Web\Purchase;

use App\Http\Controllers\Controller;

class PurchaseController extends Controller
{
    public function index()
    {
        return view('purchases.index');
    }
}
