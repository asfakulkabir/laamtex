<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbandonedOrder;

class AbandonedOrderController extends Controller
{
    public function index()
    {
        $orders = AbandonedOrder::latest()->paginate(25);
        return view('admin.abandoned-orders.index', compact('orders'));
    }
}
