<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CouponController extends Controller
{
    public function index(Request $request)
    {
        $query = Coupon::query()->withCount('orders');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        $coupons = $query->latest()->paginate(15)->withQueryString();

        return view('admin.coupons.index', compact('coupons'));
    }

    public function create()
    {
        return view('admin.coupons.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateCoupon($request);

        Coupon::create($data + ['used_count' => 0]);

        return redirect()->route('admin.coupons.index')
            ->with('success', "Coupon {$data['code']} created successfully.");
    }

    public function edit(Coupon $coupon)
    {
        return view('admin.coupons.edit', compact('coupon'));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $data = $this->validateCoupon($request, $coupon);

        $coupon->update($data);

        return redirect()->route('admin.coupons.index')
            ->with('success', "Coupon {$coupon->code} updated successfully.");
    }

    public function destroy(Coupon $coupon)
    {
        $code = $coupon->code;
        $coupon->delete();

        return redirect()->route('admin.coupons.index')
            ->with('success', "Coupon {$code} deleted. Past orders keep their discount record.");
    }

    private function validateCoupon(Request $request, ?Coupon $coupon = null): array
    {
        $data = $request->validate([
            'code'     => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9_-]+$/', Rule::unique('coupons', 'code')->ignore($coupon?->id)],
            'description' => ['nullable', 'string', 'max:255'],
            'type'     => ['required', Rule::in([Coupon::TYPE_PERCENT, Coupon::TYPE_FIXED])],
            'value'    => [
                'required',
                'numeric',
                'min:0',
                Rule::when($request->input('type') === Coupon::TYPE_PERCENT, ['max:100']),
            ],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'max_discount_amount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit'   => ['nullable', 'integer', 'min:1'],
            'per_user_limit' => ['nullable', 'integer', 'min:1'],
            'is_active' => ['nullable', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ], [
            'code.regex' => 'The code may only contain letters, numbers, dashes and underscores.',
            'value.max'  => 'A percentage discount cannot be more than 100.',
        ]);

        // Unchecked checkboxes are simply missing from the input.
        $data['is_active'] = $request->boolean('is_active');

        // A maximum discount cap only makes sense for percentage coupons.
        if ($data['type'] !== Coupon::TYPE_PERCENT) {
            unset($data['max_discount_amount']);
        }

        // Turn blank form fields into real nulls.
        foreach (['description', 'min_order_amount', 'max_discount_amount', 'usage_limit', 'per_user_limit', 'starts_at', 'expires_at'] as $key) {
            if (array_key_exists($key, $data) && blank($data[$key])) {
                $data[$key] = null;
            }
        }

        return $data;
    }
}
