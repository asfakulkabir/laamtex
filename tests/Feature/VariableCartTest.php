<?php

namespace Tests\Feature;

use App\Models\Attribute;
use App\Models\DeliveryCharge;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\User;
use App\Services\VariationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VariableCartTest extends TestCase
{
    use RefreshDatabase;

    private function variationProduct(): array
    {
        $product = Product::create([
            'user_id' => User::factory()->create(['role' => User::ROLE_MODERATOR])->id,
            'name' => 'Variable Dress',
            'slug' => 'variable-dress',
            'product_type' => 'variable',
            'regular_price' => '999.00',
            'is_active' => true,
        ]);

        $size = Attribute::create(['name' => 'Size', 'type' => Attribute::TYPE_SELECT]);
        $small = $size->values()->create(['name' => 'S']);
        $medium = $size->values()->create(['name' => 'M']);

        $service = app(VariationService::class);
        $service->syncAttributes($product, [
            ['attribute_id' => $size->id, 'value_ids' => [$small->id, $medium->id], 'is_variation' => 1, 'is_visible' => 1],
        ]);

        $sizeAttribute = $product->productAttributes()->where('attribute_id', $size->id)->first();

        $service->saveVariations($product, [
            ['values' => [$sizeAttribute->id => $small->id], 'regular_price' => '30.00', 'manage_stock' => 1, 'stock_quantity' => 5, 'status' => ProductVariation::STATUS_PUBLISH],
            ['values' => [$sizeAttribute->id => $medium->id], 'regular_price' => '35.00', 'manage_stock' => 1, 'stock_quantity' => 2, 'status' => ProductVariation::STATUS_PUBLISH],
        ]);
        $service->refreshPriceRange($product);

        return [$product->refresh(), $sizeAttribute, $small, $medium];
    }

    public function test_adding_a_variation_uses_the_variation_price_not_the_parent()
    {
        [$product, , $small] = $this->variationProduct();
        $variation = $product->variations()->firstWhere('regular_price', 30);

        $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'variation_id' => $variation->id,
            'quantity' => 2,
        ])->assertRedirect(route('cart'));

        $cart = session('cart');
        $this->assertCount(1, $cart);
        $item = array_values($cart)[0];

        $this->assertEquals(30, $item['price'], 'cart must use the variation price');
        $this->assertNotEquals(999, $item['price'], 'cart must not fall back to the parent price');
        $this->assertSame('S', $item['variation_details']);
        $this->assertEquals($small->id, $variation->attributeValues->first()->attribute_value_id);
    }

    public function test_a_variable_product_requires_a_variation()
    {
        [$product] = $this->variationProduct();

        $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertSessionHas('error');

        $this->assertEmpty(session('cart'));
    }

    public function test_a_variation_from_another_product_is_rejected()
    {
        [$product] = $this->variationProduct();
        [$otherProduct] = $this->variationProduct();
        $foreignVariation = $otherProduct->variations()->first();

        $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'variation_id' => $foreignVariation->id,
            'quantity' => 1,
        ])->assertSessionHas('error');

        $this->assertEmpty(session('cart'));
    }

    public function test_an_unpublished_variation_cannot_be_added()
    {
        [$product] = $this->variationProduct();
        $variation = $product->variations()->first();
        $variation->update(['status' => ProductVariation::STATUS_PRIVATE]);

        $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'variation_id' => $variation->id,
            'quantity' => 1,
        ])->assertSessionHas('error');

        $this->assertEmpty(session('cart'));
    }

    public function test_cart_quantity_cannot_exceed_the_variation_stock()
    {
        [$product] = $this->variationProduct();
        $variation = $product->variations()->firstWhere('regular_price', 35); // 2 in stock

        $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'variation_id' => $variation->id,
            'quantity' => 5,
        ])->assertSessionHas('error');

        $this->assertEmpty(session('cart'));
    }

    public function test_checkout_snapshots_the_variation_and_reduces_its_stock()
    {
        [$product] = $this->variationProduct();
        $variation = $product->variations()->firstWhere('regular_price', 30);

        $zone = DeliveryCharge::create(['zone' => 'Inside Dhaka', 'charge' => 60]);

        $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'variation_id' => $variation->id,
            'quantity' => 2,
        ])->assertRedirect(route('cart'));

        $this->post(route('checkout.place'), [
            'customer_name' => 'Jane Doe',
            'customer_phone' => '01712345678',
            'customer_address' => '123 Test Street, Dhaka',
            'delivery_zone' => $zone->zone,
            'payment_method' => 'cod',
        ])->assertRedirect();

        $order = Order::first();
        $this->assertNotNull($order);

        $item = $order->items()->first();
        $this->assertSame($variation->id, $item->product_variation_id);
        $this->assertSame($product->id, $item->product_id);
        $this->assertEquals(30, $item->price, 'order item must snapshot the variation price');
        $this->assertEquals(2, $item->quantity);
        $this->assertSame('S', $item->variation_details);

        $variation->refresh();
        $this->assertEquals(3, $variation->stock_quantity, 'variation stock must drop from 5 to 3');

        $this->assertNull(session('cart'));
    }

    public function test_the_variation_image_is_used_in_the_cart()
    {
        Storage::fake('public');
        [$product] = $this->variationProduct();
        $variation = $product->variations()->firstWhere('regular_price', 30);
        $variation->update(['image' => 'variations/small.png']);

        $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'variation_id' => $variation->id,
            'quantity' => 1,
        ])->assertRedirect(route('cart'));

        $item = array_values(session('cart'))[0];
        $this->assertSame('variations/small.png', $item['image']);
    }

    public function test_a_wildcard_variation_can_be_added_to_the_cart()
    {
        $product = Product::create([
            'user_id' => User::factory()->create(['role' => User::ROLE_MODERATOR])->id,
            'name' => 'Any Size Shirt',
            'slug' => 'any-size-shirt',
            'product_type' => 'variable',
            'regular_price' => '999.00',
            'is_active' => true,
        ]);

        $size = Attribute::create(['name' => 'Size', 'type' => Attribute::TYPE_SELECT]);
        $small = $size->values()->create(['name' => 'S']);
        $medium = $size->values()->create(['name' => 'M']);

        $service = app(VariationService::class);
        $service->syncAttributes($product, [
            ['attribute_id' => $size->id, 'value_ids' => [$small->id, $medium->id], 'is_variation' => 1, 'is_visible' => 1],
        ]);
        $sizeAttribute = $product->productAttributes()->where('attribute_id', $size->id)->first();

        $service->saveVariations($product, [
            ['values' => [$sizeAttribute->id => ''], 'regular_price' => '20.00', 'manage_stock' => 1, 'stock_quantity' => 9, 'status' => ProductVariation::STATUS_PUBLISH],
        ]);

        $variation = $product->variations()->first();
        $this->assertTrue($variation->attributeValues->first()->isAny());

        $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'variation_id' => $variation->id,
            'quantity' => 1,
        ])->assertRedirect(route('cart'));

        $item = array_values(session('cart'))[0];
        $this->assertEquals(20, $item['price']);
    }
}
