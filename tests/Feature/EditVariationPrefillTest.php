<?php
namespace Tests\Feature;

use App\Models\Attribute;
use App\Models\Product;
use App\Models\User;
use App\Services\VariationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditVariationPrefillTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(): array
    {
        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
        $size = Attribute::create(['name' => 'Size', 'type' => Attribute::TYPE_SELECT]);
        $size->values()->createMany([['name' => 'S', 'sort_order' => 1], ['name' => 'M', 'sort_order' => 2]]);
        $color = Attribute::create(['name' => 'Color', 'type' => Attribute::TYPE_COLOR]);
        $color->values()->createMany([['name' => 'Red', 'sort_order' => 1], ['name' => 'Blue', 'sort_order' => 2]]);

        $product = Product::create([
            'user_id' => $admin->id, 'name' => 'Shirt', 'slug' => 'shirt',
            'product_type' => 'variable', 'regular_price' => '100.00', 'stock_quantity' => 0, 'is_active' => true,
        ]);

        $service = app(VariationService::class);
        $service->syncAttributes($product, [
            ['attribute_id' => $size->id, 'value_ids' => $size->values->pluck('id')->all(), 'is_variation' => 1, 'is_visible' => 1],
            ['attribute_id' => $color->id, 'value_ids' => $color->values->pluck('id')->all(), 'is_variation' => 1, 'is_visible' => 1],
        ]);
        $service->generateVariations($product);

        return [$admin, $product, $service, $size, $color];
    }

    public function test_edit_page_ships_each_variations_selected_values()
    {
        [$admin, $product] = $this->makeProduct();

        $html = $this->actingAs($admin)->get(route('admin.products.edit', $product))->getContent();

        preg_match('/variations: (\[.*?\]),/s', $html, $m);
        $rows = json_decode($m[1], true);

        $this->assertCount(4, $rows);
        foreach ($rows as $row) {
            $this->assertNotEmpty($row['values'], 'every generated row must carry its selected values');
            $this->assertNotEmpty($row['label'], 'every generated row must carry a readable label');
        }

        $labels = array_column($rows, 'label');
        $this->assertEqualsCanonicalizing(['S / Red', 'S / Blue', 'M / Red', 'M / Blue'], $labels);
    }

    public function test_options_bind_selected_so_the_dropdown_is_not_blank()
    {
        [$admin, $product] = $this->makeProduct();

        $html = $this->actingAs($admin)->get(route('admin.products.edit', $product))->getContent();

        // The value binding on <select> runs before x-for renders <option>,
        // so each option has to mark itself selected instead.
        $this->assertStringContainsString(':selected="String(o.id) === String(v.values[pa.product_attribute_id] ?? \'\')"', $html);
        $this->assertStringNotContainsString(':value="v.values[pa.product_attribute_id]', $html);
    }

    public function test_saving_the_edit_form_keeps_every_variation()
    {
        [$admin, $product, $service, $size, $color] = $this->makeProduct();

        // Rebuild the exact payload the pre-filled edit form posts: every row
        // carries its variation id plus the value map it was rendered with.
        $variationRows = [];

        foreach ($product->variations()->get() as $i => $variation) {
            $values = [];

            foreach ($variation->attributeValues as $av) {
                $values[$av->product_attribute_id] = $av->attribute_value_id;
            }

            $variationRows[] = [
                'id' => $variation->id,
                'values' => $values,
                'regular_price' => '50.00',
                'manage_stock' => 1,
                'stock_quantity' => 3,
                'status' => 'publish',
            ];
        }

        $this->actingAs($admin)->put(route('admin.products.update', $product), [
            'name' => 'Shirt',
            'product_type' => 'variable',
            'regular_price' => '100.00',
            'stock_status' => 'instock',
            'variations' => $variationRows,
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertSame(4, $product->variations()->count());
        $this->assertEqualsCanonicalizing(
            ['50.00'],
            $product->variations()->distinct()->pluck('regular_price')->all()
        );

        $labels = $product->variations()->with('attributeValues')->get()
            ->map(fn ($v) => $v->attribute_label)->all();

        $this->assertEqualsCanonicalizing(
            ['S / Red', 'S / Blue', 'M / Red', 'M / Blue'],
            $labels,
            'no combination may be dropped on re-save'
        );
    }
}
