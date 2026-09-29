<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\AttributeValue;
use App\Models\Coupon;
use App\Models\DeliveryCharge;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductVariation;
use App\Models\Setting;
use App\Models\Slider;
use App\Models\Testimonial;
use App\Mail\NewOrderNotification;
use App\Services\MetaPixelService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class StoreController extends Controller
{
    public function index()
    {
        $featuredCategories = Category::whereNull('parent_id')->limit(6)->get();
        $featuredProducts = Product::with(['images', 'categories'])->where('is_active', true)->where('is_featured', true)->orderBy('sort_order')->latest()->limit(8)->get();
        $latestProducts = Product::with(['images', 'categories'])->where('is_active', true)->orderBy('sort_order')->latest()->limit(8)->get();
        $sliders = Slider::active()->get();
        $mediaSliders = $sliders->filter(fn (Slider $slider) => $slider->isVideo() || $slider->isAudio())->values();
        if ($mediaSliders->isNotEmpty()) {
            $sliders = $mediaSliders;
        }

        $testimonials = Testimonial::active()->get();

        return view('store.index', compact('featuredCategories', 'featuredProducts', 'latestProducts', 'sliders', 'testimonials'));
    }

    public function categories()
    {
        $categories = Category::whereNull('parent_id')->with('children')->orderBy('name')->get();

        return view('store.categories', compact('categories'));
    }

    public function shop(Request $request)
    {
        $query = Product::with(['images', 'categories'])->where('is_active', true);

        // Category filter
        if ($request->filled('category')) {
            $catSlug = $request->input('category');
            $category = Category::where('slug', $catSlug)->first();
            if ($category) {
                // Get all descendant category IDs (recursive)
                $categoryIds = $this->getDescendantCategoryIds($category);
                $query->whereHas('categories', function ($q) use ($categoryIds) {
                    $q->whereIn('categories.id', $categoryIds);
                });
            }
        }

        // Search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%");
        }

        // Price range bounds for slider (based on current category/search scope)
        // Variable products use their cached min/max, which the variation
        // service keeps in step with the purchasable variations.
        $priceBounds = (clone $query)
            ->toBase()
            ->selectRaw('MIN(COALESCE(products.min_price, products.sale_price, products.regular_price, 0)) as min_price')
            ->selectRaw('MAX(COALESCE(products.max_price, products.sale_price, products.regular_price, 0)) as max_price')
            ->first();

        $minPrice = (int) floor(($priceBounds->min_price ?? 0) / 100) * 100;
        $maxPrice = (int) ceil(($priceBounds->max_price ?? 10000) / 100) * 100;
        if ($maxPrice <= $minPrice) {
            $maxPrice = $minPrice + 100;
        }

        // Price filter
        if ($request->filled('min_price')) {
            $query->where(function ($q) use ($request) {
                // For simple products
                $q->where('regular_price', '>=', $request->input('min_price'))
                  // A variable product qualifies when any variation reaches it.
                  ->orWhereHas('variations', function ($qv) use ($request) {
                      $qv->where('status', 'publish')
                         ->whereNotNull('regular_price')
                         ->where('regular_price', '>=', $request->input('min_price'));
                  });
            });
        }
        if ($request->filled('max_price')) {
            $query->where(function ($q) use ($request) {
                $q->where(function ($qs) use ($request) {
                    $qs->whereNotNull('regular_price')
                       ->where('regular_price', '<=', $request->input('max_price'));
                })->orWhereHas('variations', function ($qv) use ($request) {
                    $qv->where('status', 'publish')
                       ->whereNotNull('regular_price')
                       ->where('regular_price', '<=', $request->input('max_price'));
                });
            });
        }

        // Sorting
        $sort = $request->input('sort', 'latest');
        if ($sort === 'price_asc') {
            $query->orderByRaw('COALESCE(products.min_price, products.sale_price, products.regular_price, 0) asc');
        } elseif ($sort === 'price_desc') {
            $query->orderByRaw('COALESCE(products.max_price, products.sale_price, products.regular_price, 0) desc');
        } else {
            $query->orderBy('products.sort_order')->orderBy('products.created_at', 'desc');
        }

        $products = $query->paginate(12)->withQueryString();
        $categories = Category::whereNull('parent_id')->with('children')->orderBy('name')->get();

        // Send Search to Conversion API
        if ($request->filled('search')) {
            app(MetaPixelService::class)->sendEvent('Search', [
                'search_string' => $request->input('search'),
                'content_type' => 'product',
                'currency' => 'BDT',
            ]);
        }

        return view('store.shop', compact('products', 'categories', 'minPrice', 'maxPrice'));
    }

    public function product($slug)
    {
        $product = Product::with([
            'images',
            'categories',
            'variations.attributeValues',
            'productAttributes.values',
        ])->where('slug', $slug)->where('is_active', true)->firstOrFail();
        
        // Find related products
        $categoryIds = $product->categories->pluck('id')->toArray();
        $relatedProducts = Product::with('images')
            ->where('id', '!=', $product->id)
            ->where('is_active', true)
            ->whereHas('categories', function ($q) use ($categoryIds) {
                $q->whereIn('categories.id', $categoryIds);
            })
            ->limit(4)
            ->get();

        $variationsJson = [];
        $attributesJson = [];

        if ($product->product_type === 'variable') {
            // Published variations are offered to customers, including the ones
            // that cannot be bought yet, so the page can explain why instead of
            // showing an option that the cart would reject.
            $variations = $product->publishedVariations()->get();

            $variationsJson = $variations->map(function (ProductVariation $variation) {
                // Keyed by product attribute id so the storefront can look a
                // variation up by the shopper's selection. A null value means
                // the variation accepts any option for that attribute.
                $values = [];

                foreach ($variation->valuesByProductAttribute() as $attributeId => $value) {
                    $values[$attributeId] = $value->attribute_value_id;
                }

                return [
                    'id' => $variation->id,
                    'values' => $values,
                    'price' => $variation->active_price,
                    'stock' => $variation->isStockTracked() ? (int) $variation->effectiveStockQuantity() : null,
                    'in_stock' => $variation->isInStock(),
                    'purchasable' => $variation->isPurchasable(),
                    'unavailable_reason' => $variation->unavailabilityReason(),
                    'sku' => $variation->sku,
                    'image' => $variation->image ? Storage::url($variation->image) : null,
                ];
            })->values();

            // The option pickers, in the order the product defines them.
            $attributesJson = $product->productAttributes
                ->filter(fn (ProductAttribute $attribute) => $attribute->is_visible && $attribute->values->isNotEmpty())
                ->map(fn (ProductAttribute $attribute) => [
                    'id' => $attribute->id,
                    'name' => $attribute->display_name,
                    'type' => $attribute->type,
                    'is_variation' => (bool) $attribute->is_variation,
                    'values' => $attribute->values->map(fn (AttributeValue $value) => [
                        'id' => $value->id,
                        'name' => $value->name,
                        'color' => $value->swatch_color,
                        'image' => $value->image_url,
                    ])->values(),
                ])->values();
        }

        // Send ViewContent to Conversion API
        app(MetaPixelService::class)->sendEvent('ViewContent', [
            'content_ids' => [$product->id],
            'content_name' => $product->name,
            'content_type' => 'product',
            'value' => $product->getDisplayPrice() ?: 0,
            'currency' => 'BDT',
        ]);

        return view('store.product', compact('product', 'relatedProducts', 'variationsJson', 'attributesJson'));
    }

    public function cart()
    {
        $cart = session()->get('cart', []);
        return view('store.cart', compact('cart'));
    }

    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'variation_id' => 'nullable|exists:product_variations,id',
        ]);

        $product = Product::findOrFail($request->input('product_id'));
        $qty = $request->input('quantity');
        $variationId = $request->input('variation_id');
        $variation = null;

        if ($product->product_type === 'variable') {
            if (!$variationId) {
                return back()->with('error', 'Please select a product option.');
            }

            $variation = ProductVariation::with('attributeValues')->findOrFail($variationId);

            if ($variation->product_id !== $product->id) {
                return back()->with('error', 'That option does not belong to this product.');
            }

            if (! $variation->isPurchasable()) {
                return back()->with('error', $variation->unavailabilityReason() ?? 'Sorry, this option is currently unavailable.');
            }

            $available = (int) $variation->effectiveStockQuantity();

            if ($variation->isStockTracked() && $available < $qty) {
                return back()->with('error', "Only {$available} items left in stock for this option.");
            }

            // A variation never falls back to the parent price: the parent of a
            // variable product has no price of its own.
            $price = $variation->active_price;

            $variationDetails = $variation->attribute_label ?: null;
            $cartKey = "product_{$product->id}_var_{$variationId}";
        } else {
            if ($product->manage_stock && $product->stock_quantity < $qty) {
                return back()->with('error', "Only {$product->stock_quantity} items left in stock.");
            }
            $price = $product->getDisplayPrice();
            $variationDetails = null;
            $cartKey = "product_{$product->id}";
        }

        $cart = session()->get('cart', []);

        if (isset($cart[$cartKey])) {
            $newQty = $cart[$cartKey]['quantity'] + $qty;
            // Validate stock again. Untracked stock has no ceiling.
            if ($variation && $variation->isStockTracked() && (int) $variation->effectiveStockQuantity() < $newQty) {
                return back()->with('error', "Cannot add more. Only {$variation->effectiveStockQuantity()} items available in total.");
            } elseif (!$variation && $product->manage_stock && $product->stock_quantity < $newQty) {
                return back()->with('error', "Cannot add more. Only {$product->stock_quantity} items available in total.");
            }
            $cart[$cartKey]['quantity'] = $newQty;
        } else {
            $featuredImage = $product->images->where('is_featured', true)->first();
            $imagePath = $variation && $variation->image
                ? $variation->image
                : ($featuredImage ? $featuredImage->image : null);

            $cart[$cartKey] = [
                'product_id' => $product->id,
                'variation_id' => $variationId,
                'name' => $product->name,
                'image' => $imagePath,
                'price' => $price,
                'quantity' => $qty,
                'variation_details' => $variationDetails,
            ];
        }

        session()->put('cart', $cart);

        // Fire AddToCart Pixel and Conversion API
        $pixelEvent = [
            'event' => 'AddToCart',
            'data' => [
                'content_ids' => [$product->id],
                'content_name' => $product->name,
                'content_type' => 'product',
                'value' => (float) $price,
                'currency' => 'BDT',
            ],
        ];
        session()->push('pixel_events', $pixelEvent);
        app(MetaPixelService::class)->sendEvent('AddToCart', $pixelEvent['data']);

        if ($request->input('buy_now') == '1') {
            return redirect()->route('checkout');
        }

        return redirect()->route('cart')->with('success', 'Product added to cart successfully!');
    }

    public function updateCart(Request $request)
    {
        $request->validate([
            'key' => 'required|string',
            'quantity' => 'required|integer|min:1',
        ]);

        $key = $request->input('key');
        $qty = $request->input('quantity');

        $cart = session()->get('cart', []);

        if (!isset($cart[$key])) {
            return response()->json(['success' => false, 'message' => 'Item not found in cart.'], 404);
        }

        $cartItem = $cart[$key];
        $product = Product::findOrFail($cartItem['product_id']);

        if ($cartItem['variation_id']) {
            $variation = ProductVariation::findOrFail($cartItem['variation_id']);
            $available = (int) $variation->effectiveStockQuantity();
            if ($variation->isStockTracked() && $available < $qty) {
                return response()->json([
                    'success' => false,
                    'message' => "Only {$available} items available for this option."
                ], 422);
            }
        } else {
            if ($product->manage_stock && $product->stock_quantity < $qty) {
                return response()->json([
                    'success' => false, 
                    'message' => "Only {$product->stock_quantity} items available."
                ], 422);
            }
        }

        $cart[$key]['quantity'] = $qty;
        session()->put('cart', $cart);

        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        return response()->json([
            'success' => true,
            'message' => 'Cart updated successfully.',
            'item_total' => number_format($cart[$key]['price'] * $qty, 2),
            'cart_subtotal' => number_format($subtotal, 2),
            'cart_count' => count($cart),
        ]);
    }

    public function removeFromCart(Request $request)
    {
        $request->validate([
            'key' => 'required|string',
        ]);

        $key = $request->input('key');
        $cart = session()->get('cart', []);

        if (isset($cart[$key])) {
            unset($cart[$key]);
            session()->put('cart', $cart);
        }

        if ($request->ajax()) {
            $subtotal = 0;
            foreach ($cart as $item) {
                $subtotal += $item['price'] * $item['quantity'];
            }
            return response()->json([
                'success' => true,
                'message' => 'Item removed from cart.',
                'cart_subtotal' => number_format($subtotal, 2),
                'cart_count' => count($cart),
            ]);
        }

        return redirect()->route('cart')->with('success', 'Item removed from cart.');
    }

    public function checkout()
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart')->with('error', 'Your cart is empty.');
        }

        $subtotal = array_sum(array_map(fn($item) => $item['price'] * $item['quantity'], $cart));
        $deliveryZones = DeliveryCharge::orderBy('zone')->get();
        $customer = auth()->user();

        // Re-check the coupon kept in the session against the live cart and
        // drop it from the session when it is no longer valid.
        [$coupon, $discount] = $this->resolveAppliedCoupon((float) $subtotal);
        if (!$coupon) {
            session()->forget('checkout_coupon_code');
        }

        // Send InitiateCheckout + AddPaymentInfo to Conversion API
        $contentIds = array_values(array_map(fn($item) => $item['product_id'], $cart));
        app(MetaPixelService::class)->sendEvent('AddPaymentInfo', [
            'content_type' => 'product',
            'value' => (float) $subtotal,
            'currency' => 'BDT',
        ]);
        app(MetaPixelService::class)->sendEvent('InitiateCheckout', [
            'content_ids' => array_values($contentIds),
            'content_type' => 'product',
            'value' => (float) $subtotal,
            'currency' => 'BDT',
            'num_items' => count($cart),
        ]);

        return view('store.checkout', compact('cart', 'deliveryZones', 'subtotal', 'customer', 'coupon', 'discount'));
    }

    /**
     * Apply a coupon code to the current cart (AJAX from the checkout page).
     */
    public function applyCoupon(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'max:50'],
        ]);

        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return response()->json(['success' => false, 'message' => 'Your cart is empty.'], 422);
        }

        $subtotal = (float) array_sum(array_map(fn($item) => $item['price'] * $item['quantity'], $cart));

        $coupon = Coupon::whereRaw('UPPER(code) = ?', [strtoupper(trim($request->input('code')))])->first();
        if (!$coupon) {
            return response()->json(['success' => false, 'message' => 'Invalid coupon code.'], 422);
        }

        if ($reason = $coupon->rejectionReason($subtotal, auth()->id())) {
            return response()->json(['success' => false, 'message' => $reason], 422);
        }

        $discount = $coupon->discountFor($subtotal);

        session(['checkout_coupon_code' => $coupon->code]);

        return response()->json([
            'success' => true,
            'code'    => $coupon->code,
            'label'   => $coupon->value_label,
            'discount' => $discount,
            'discount_label' => '-৳' . number_format($discount, 0),
        ]);
    }

    public function removeCoupon()
    {
        session()->forget('checkout_coupon_code');

        return response()->json(['success' => true]);
    }

    /**
     * Load the coupon stored in the session and work out its discount.
     *
     * @return array{0: ?Coupon, 1: float}
     */
    private function resolveAppliedCoupon(float $subtotal): array
    {
        $code = session('checkout_coupon_code');
        if (!$code) {
            return [null, 0.0];
        }

        $coupon = Coupon::whereRaw('UPPER(code) = ?', [strtoupper($code)])->first();
        if (!$coupon || $coupon->rejectionReason($subtotal, auth()->id())) {
            return [null, 0.0];
        }

        return [$coupon, $coupon->discountFor($subtotal)];
    }

    public function placeOrder(Request $request)
    {
        $request->validate([
            'customer_name'    => 'required|string|max:255',
            'customer_phone'   => ['required', 'string', 'regex:/^(?:\+?88)?01[3-9]\d{8}$/'],
            'customer_address' => 'required|string',
            'delivery_zone'    => 'required|exists:delivery_charges,zone',
            'payment_method'   => 'required|in:cod,bkash',
            'bkash_sender_last4' => Rule::when(
                $request->input('payment_method') === 'bkash',
                ['required', 'digits:4'],
                'nullable'
            ),
        ]);

        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart')->with('error', 'Your cart is empty.');
        }

        $delivery = DeliveryCharge::where('zone', $request->delivery_zone)->firstOrFail();

        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        $subtotal = (float) $subtotal;

        // The coupon is re-validated here so a tampered or expired session
        // value can never change the price that gets stored.
        [$coupon, $discount] = $this->resolveAppliedCoupon($subtotal);

        $totalAmount = (int) round(max(0, $subtotal - $discount + $delivery->charge));

        $itemsArray = [];
        foreach ($cart as $item) {
            $itemsArray[] = [
                'product_id'        => $item['product_id'],
                'variation_id'      => $item['variation_id'],
                'name'              => $item['name'],
                'price'             => $item['price'],
                'quantity'          => $item['quantity'],
                'variation_details' => $item['variation_details'],
                'image'             => $item['image'] ?? null,
            ];
        }

        DB::beginTransaction();

        try {
            foreach ($cart as $item) {
                $product = Product::findOrFail($item['product_id']);

                if ($item['variation_id']) {
                    $variation = ProductVariation::findOrFail($item['variation_id']);
                    if ($variation->isStockTracked() && (int) $variation->effectiveStockQuantity() < $item['quantity']) {
                        throw new \Exception("{$product->name} ({$item['variation_details']}) is out of stock.");
                    }
                } else {
                    if ($product->manage_stock && $product->stock_quantity < $item['quantity']) {
                        throw new \Exception("{$product->name} is out of stock.");
                    }
                }
            }

            $order = Order::create([
                'user_id'             => auth()->id(),
                'items_json'          => json_encode($itemsArray),
                'payment_method'      => $request->payment_method === 'bkash' ? 'bKash Send Money' : 'Cash on Delivery',
                'bkash_sender_last4'  => $request->payment_method === 'bkash' ? $request->bkash_sender_last4 : null,
                'customer_name'       => $request->customer_name,
                'customer_phone'      => $request->customer_phone,
                'customer_address'    => $request->customer_address,
                'delivery_charge_id'  => $delivery->id,
                'coupon_id'           => $coupon?->id,
                'coupon_code'         => $coupon?->code,
                'discount_amount'     => $discount,
                'total_amount'        => $totalAmount,
                'total'               => $totalAmount,
                'status'              => 'processing',
                'is_sent_to_steadfast'=> false,
                'is_notification_sent'=> false,
            ]);

            foreach ($cart as $item) {
                $product = Product::findOrFail($item['product_id']);

                OrderItem::create([
                    'order_id'             => $order->id,
                    'product_id'           => $product->id,
                    'product_variation_id' => $item['variation_id'],
                    'product_name'         => $product->name,
                    'quantity'             => $item['quantity'],
                    'price'                => $item['price'],
                    'variation_details'    => $item['variation_details'],
                ]);

                if ($item['variation_id']) {
                    $variation = ProductVariation::findOrFail($item['variation_id']);
                    $variation->reduceStock($item['quantity']);
                    $product->updateStockFromVariations();
                } else {
                    $product->decrement('stock_quantity', $item['quantity']);
                }
            }

            DB::commit();

            if ($coupon) {
                $coupon->increment('used_count');
            }

            session()->forget('cart');
            session()->forget('checkout_coupon_code');

            // Flash purchase pixel data (consumed once in orderSuccess)
            $pixelContentIds = array_map(fn($item) => $item['product_id'], $cart);
            session()->flash('pixel_purchase', [
                'value' => (float) $totalAmount,
                'currency' => 'BDT',
                'content_ids' => array_values($pixelContentIds),
                'content_type' => 'product',
            ]);

            return redirect()->route('order.success', $order->id)
                ->with('success', 'Order placed successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to place order: ' . $e->getMessage())->withInput();
        }
    }

    public function orderSuccess(Order $order)
    {
        // Load the items relationship explicitly (not the attribute)
        $order->load('items', 'deliveryCharge');

        // Send Purchase to Conversion API (one-time, never on refresh)
        $purchaseData = session()->get('pixel_purchase', null);
        if ($purchaseData) {
            app(MetaPixelService::class)->sendEvent('Purchase', $purchaseData);
        }

        if (!$order->is_notification_sent) {
            try {
                $emails = Setting::getValue('order_notification_emails', '');
                \Log::info('Order notification emails from setting: ' . $emails);
                
                if ($emails) {
                    $recipients = array_map('trim', explode(',', $emails));
                    $recipients = array_filter($recipients);
                    \Log::info('Processing order #' . $order->id . ' notification for recipients: ' . implode(', ', $recipients));
                    
                    foreach ($recipients as $recipient) {
                        try {
                            \Log::info('Sending email to: ' . $recipient);
                            Mail::to($recipient)->send(new NewOrderNotification($order));
                            \Log::info('Email sent successfully to: ' . $recipient);
                        } catch (\Exception $emailError) {
                            \Log::error('Failed to send email to ' . $recipient . ': ' . $emailError->getMessage());
                        }
                    }
                    $order->update(['is_notification_sent' => true]);
                } else {
                    \Log::warning('Order notification emails not configured in settings for order #' . $order->id);
                }
            } catch (\Exception $e) {
                \Log::error('Failed to process order notification for order ' . $order->id . ': ' . $e->getMessage());
            }
        }

        return view('store.order-success', compact('order'));
    }

    // Helpers
    private function getDescendantCategoryIds(Category $category)
    {
        $ids = [$category->id];
        foreach ($category->children as $child) {
            $ids = array_merge($ids, $this->getDescendantCategoryIds($child));
        }
        return $ids;
    }
}
