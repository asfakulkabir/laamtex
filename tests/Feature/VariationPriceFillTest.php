<?php

namespace Tests\Feature;

use App\Models\Attribute;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The "Fill All Rows" / "Fill Selected" price buttons on the product Variations
 * tab. They only copy values into the already-submitted variation fields, so
 * the ordinary Save button is what persists them. These tests lock down that
 * the page still renders, that the form contract the buttons rely on is
 * present, and that saving a filled-in form prices the variations for real.
 */
class VariationPriceFillTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
    }

    private function product(): Product
    {
        $product = Product::create([
            'user_id' => $this->admin()->id,
            'name' => 'Hair Dryer',
            'slug' => 'hair-dryer',
            'product_type' => 'variable',
            'regular_price' => '1200.00',
            'stock_quantity' => 0,
            'is_active' => true,
        ]);

        $color = Attribute::create(['name' => 'Color', 'type' => Attribute::TYPE_COLOR]);
        $pink = $color->values()->create(['name' => 'Pink', 'color_code' => '#ffc0cb', 'sort_order' => 1]);
        $purple = $color->values()->create(['name' => 'Purple', 'color_code' => '#a020f0', 'sort_order' => 2]);

        $productAttribute = $product->productAttributes()->create([
            'attribute_id' => $color->id,
            'position' => 1,
            'is_visible' => true,
            'is_variation' => true,
        ]);
        // The product attribute carries the options a row may select, which is
        // what the save path validates a submitted combination against.
        $productAttribute->values()->attach([$pink->id, $purple->id]);

        // Two published variations with no price, the state that made customers
        // see "Sorry, this option is currently unavailable".
        foreach ([$pink, $purple] as $value) {
            $variation = $product->variations()->create([
                'combo_key' => "1:{$value->id}",
                'regular_price' => null,
                'manage_stock' => true,
                'stock_quantity' => 5,
                'stock_status' => ProductVariation::STOCK_IN_STOCK,
                'status' => ProductVariation::STATUS_PUBLISH,
            ]);
            $variation->attributeValues()->create([
                'product_attribute_id' => $productAttribute->id,
                'attribute_value_id' => $value->id,
            ]);
        }

        return $product;
    }

    public function test_price_fill_controls_are_present_on_the_product_page()
    {
        $this->actingAs($this->admin())
            ->get(route('admin.products.edit', $this->product()))
            ->assertOk()
            ->assertSee('Fill All Rows', false)
            ->assertSee('Fill Selected', false)
            ->assertSee('bulkRegularPrice', false)
            ->assertSee('bulkSalePrice', false);
    }

    public function test_selection_state_is_tracked_so_selected_rows_can_be_targeted()
    {
        $this->actingAs($this->admin())
            ->get(route('admin.products.edit', $this->product()))
            ->assertOk()
            // The buttons read the ticked ids to know which rows to fill.
            ->assertSee('selectedVariationIds', false)
            ->assertSee('applyPriceToSelected', false);
    }

    public function test_the_price_filler_refuses_a_sale_price_that_is_not_lower()
    {
        // The rule the buttons enforce matches the server: a sale price must be
        // lower than the regular price, so a shopper never sees a "sale" that
        // is not one.
        $this->assertTrue(
            ProductVariation::make(['regular_price' => '1200.00', 'sale_price' => '1100.00'])->isOnSale()
        );
        $this->assertFalse(
            ProductVariation::make(['regular_price' => '1200.00', 'sale_price' => '1200.00'])->isOnSale()
        );
        $this->assertFalse(
            ProductVariation::make(['regular_price' => '1200.00', 'sale_price' => '1300.00'])->isOnSale()
        );
    }

    public function test_saving_a_filled_in_form_prices_the_variations_and_makes_them_buyable()
    {
        $product = $this->product();
        $admin = $this->admin();

        $this->assertSame(0, $product->publishedVariations()->get()->filter->isPurchasable()->count());

        $this->actingAs($admin)->put(route('admin.products.update', $product), [
            'name' => $product->name,
            'product_type' => 'variable',
            'regular_price' => '1200.00',
            'stock_status' => 'instock',
            'is_active' => '1',
            'variations' => $product->variations->map(fn (ProductVariation $v) => [
                'id' => $v->id,
                'values' => [
                    $v->attributeValues->first()->product_attribute_id
                        => $v->attributeValues->first()->attribute_value_id,
                ],
                // What the two buttons put in the boxes.
                'regular_price' => '1200',
                'sale_price' => '1100',
                'manage_stock' => '1',
                'stock_quantity' => 5,
                'stock_status' => 'instock',
                'status' => 'publish',
            ])->all(),
        ])->assertRedirect(route('admin.products.index'));

        $variations = $product->variations()->get();

        $this->assertCount(2, $variations);

        foreach ($variations as $variation) {
            $this->assertSame('1200.00', $variation->regular_price);
            $this->assertSame('1100.00', $variation->sale_price);
            $this->assertTrue($variation->isPurchasable(), "{$variation->attribute_label} should be buyable now.");
            $this->assertNull($variation->unavailabilityReason());
        }

        $this->assertSame(2, $product->publishedVariations()->get()->filter->isPurchasable()->count());
    }

    public function test_see_more_shows_four_products_in_a_four_column_grid()
    {
        $view = file_get_contents(resource_path('views/store/product.blade.php'));

        $this->assertStringContainsString('lg:grid-cols-4', $view, 'Desktop should show 4 across.');
        $this->assertStringNotContainsString(
            '2xl:grid-cols-5',
            $view,
            'A 5th column with only 4 products leaves a hole on wide screens.'
        );

        // The query itself is capped at 4, so the grid never leaves empty cells.
        $controller = file_get_contents(app_path('Http/Controllers/StoreController.php'));
        $this->assertMatchesRegularExpression(
            '/relatedProducts[\s\S]{0,600}?->limit\(4\)/',
            $controller,
            'Related products are limited to 4.'
        );
    }

    public function test_a_filled_in_row_without_a_sale_price_clears_any_previous_sale()
    {
        $product = $this->product();
        $admin = $this->admin();

        $variation = $product->variations()->first();
        $variation->update(['regular_price' => '1200.00', 'sale_price' => '1000.00']);

        // The Sale box left blank submits an empty string, which the variation
        // service reads as "no sale price".
        $this->actingAs($admin)->put(route('admin.products.update', $product), [
            'name' => $product->name,
            'product_type' => 'variable',
            'regular_price' => '1200.00',
            'stock_status' => 'instock',
            'is_active' => '1',
            'variations' => [[
                'id' => $variation->id,
                'values' => [
                    $variation->attributeValues->first()->product_attribute_id
                        => $variation->attributeValues->first()->attribute_value_id,
                ],
                'regular_price' => '1200',
                'sale_price' => '',
                'manage_stock' => '1',
                'stock_quantity' => 5,
                'stock_status' => 'instock',
                'status' => 'publish',
            ]],
        ])->assertRedirect(route('admin.products.index'));

        $this->assertSame('1200.00', $variation->fresh()->regular_price);
        $this->assertNull($variation->fresh()->sale_price);
        $this->assertTrue($variation->fresh()->isPurchasable());
    }

    public function test_bulk_set_regular_price_still_works_from_the_bulk_form()
    {
        $product = $this->product();

        $this->actingAs($this->admin())->post(route('admin.products.variations.bulk', $product), [
            'action' => 'set_regular_price',
            'variation_ids' => $product->variations->pluck('id')->all(),
            'params' => ['value' => '1200'],
        ])->assertRedirect();

        foreach ($product->variations()->get() as $variation) {
            $this->assertSame('1200.00', $variation->regular_price);
            $this->assertTrue($variation->isPurchasable());
        }
    }
}
