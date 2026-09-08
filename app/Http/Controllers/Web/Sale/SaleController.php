<?php

namespace App\Http\Controllers\Web\Sale;

use App\Http\Controllers\Controller;

class SaleController extends Controller
{
    public function index()
    {
        return view('sales.index');
    }
}
