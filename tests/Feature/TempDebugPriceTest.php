<?php

namespace Tests\Feature;

use App\Models\Attribute;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\User;
use App\Services\VariationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TempDebugPriceTest extends TestCase
{
    use RefreshDatabase;


    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
    }

    /** @return array{0: Product, 1: ProductAttribute} */
    private function product(string $name = 'Hair Dryer'): array
    {
        $admin = $this->admin();

        $color = Attribute::create(['name' => 'Color', 'type' => Attribute::TYPE_COLOR]);
        $pink = $color->values()->create(['name' => 'Pink', 'color_code' => '#ffc0cb']);
        $blue = $color->values()->create(['name' => 'Blue', 'color_code' => '#00f']);

        $product = Product::create([
            'user_id' => $admin->id,
            'name' => $name,
            'slug' => strtolower(str_replace(' ', '-', $name)).'-'.substr(md5($name), 0, 6),
            'product_type' => 'variable',
            'regular_price' => '100.00',
            'is_active' => true,
        ]);

        $productAttribute = $product->productAttributes()->create([
            'attribute_id' => $color->id,
            'position' => 1,
            'is_visible' => true,
            'is_variation' => true,
        ]);
        $productAttribute->values()->attach([$pink->id, $blue->id]);

        app(VariationService::class)->generateVariations($product);

        return [$product, $productAttribute];
    }

    public function test_debug_update_variation_price()
    {
        [$product, $productAttribute] = $this->product('Dryer Debug');
        $this->withoutMiddleware();

        $response = $this->actingAs($this->admin())->put(route('admin.products.update', $product), [
            'name' => 'Dryer Debug',
            'product_type' => 'variable',
            'regular_price' => '100.00',
            'is_active' => '1',
            'attributes' => [[
                'attribute_id' => $productAttribute->attribute_id,
                'value_ids' => $productAttribute->values->pluck('id')->all(),
                'is_visible' => '1',
                'is_variation' => '1',
            ]],
            'variations' => $product->variations->map(fn ($variation) => [
                'id' => $variation->id,
                'values' => $variation->attributeValues
                    ->mapWithKeys(fn ($value) => [$value->product_attribute_id => (string) $value->attribute_value_id])
                    ->all(),
                'regular_price' => '150',
                'manage_stock' => '1',
                'stock_quantity' => 4,
                'stock_status' => 'instock',
                'status' => 'publish',
            ])->values()->all(),
        ]);

        file_put_contents('/tmp/probe.log', 'STATUS: '.$response->status().' ERRORS: '.json_encode(session('errors')?->all()).PHP_EOL, FILE_APPEND);

        $this->assertTrue(true);
    }
}
