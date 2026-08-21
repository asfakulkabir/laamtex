<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $totalRevenue = Order::whereNot('status', Order::STATUS_CANCELLED)->sum('total_amount');
        $ordersCount = Order::count();
        $productsCount = Product::count();

        $outOfStockCount = Product::where('stock_quantity', '<=', 0)->count();

        $recentOrders = Order::with('deliveryCharge')->latest()->limit(5)->get();

        return view('admin.dashboard', compact(
            'totalRevenue',
            'ordersCount',
            'productsCount',
            'outOfStockCount',
            'recentOrders'
        ));
    }
}
