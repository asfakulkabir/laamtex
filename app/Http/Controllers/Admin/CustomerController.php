<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('is_admin', false)->withCount('orders');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        if ($request->filled('has_orders')) {
            $query->when($request->input('has_orders') === 'yes', function ($q) {
                $q->has('orders');
            }, function ($q) {
                $q->doesntHave('orders');
            });
        }

        $customers = $query->latest()->paginate(15)->withQueryString();

        return view('admin.customers.index', compact('customers'));
    }

    public function show(User $customer)
    {
        abort_if($customer->is_admin, 404);

        $orders = $customer->orders()->with('deliveryCharge')->latest()->get();

        return view('admin.customers.show', compact('customer', 'orders'));
    }

    public function destroy(User $customer)
    {
        abort_if($customer->is_admin, 404);

        $name = $customer->name;
        $customer->delete();

        return redirect()->route('admin.customers.index')
            ->with('success', "Customer {$name} has been deleted. Their order history remains intact.");
    }
}