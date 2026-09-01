<?php

namespace App\Http\Controllers\Web\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index()
    {
        return view('inventories.index');
    }

}
