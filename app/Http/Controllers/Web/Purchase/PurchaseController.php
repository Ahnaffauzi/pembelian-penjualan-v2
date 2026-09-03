<?php

namespace App\Http\Controllers\Web\Purchase;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function index()
    {
        return view('purchases.index');
    }

    public function create()
    {
        return view('purchases.create');
    }
}
