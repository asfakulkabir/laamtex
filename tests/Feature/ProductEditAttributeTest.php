<?php

namespace Tests\Feature;

use App\Models\Attribute;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The attribute dropdown must not read as "custom" when real global attributes
 * exist, ticking a variation value must build the combination row, and the
 * variation table must start folded away.
 */
class ProductEditAttributeTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
    }

    private function variableProduct(): Product
    {
        $product = Product::create([
            'name' => 'Attribute Tee',
            'slug' => 'attribute-tee',
            'product_type' => 'variable',
            'is_active' => true,
        ]);

        $size = Attribute::create(['name' => 'Size', 'type' => Attribute::TYPE_SELECT]);
        $m = $size->values()->create(['name' => 'M']);
        $l = $size->values()->create(['name' => 'L']);

        $product->productAttributes()->create([
            'attribute_id' => $size->id,
            'is_visible' => true,
            'is_variation' => true,
        ])->values()->attach([
            ['attribute_value_id' => $m->id],
            ['attribute_value_id' => $l->id],
        ]);

        return $product;
    }

    public function test_the_attribute_dropdown_lists_the_global_attributes()
    {
        $product = $this->variableProduct();

        $html = $this->actingAs($this->admin())
            ->get(route('admin.products.edit', $product))
            ->assertOk()
            ->getContent();

        // The empty option must not read as if only custom attributes exist.
        $this->assertStringContainsString('— Select attribute —', $html);
        $this->assertStringNotContainsString('— Custom attribute —', $html);
        $this->assertStringContainsString('Size', $html);
    }

    public function test_ticking_a_variation_value_builds_the_missing_combination_rows()
    {
        $product = $this->variableProduct();

        $html = $this->actingAs($this->admin())
            ->get(route('admin.products.edit', $product))
            ->assertOk()
            ->getContent();

        // Value chips re-run the combination builder, so adding a Size or Color
        // adds a row instead of only updating the attribute chip.
        $this->assertStringContainsString('@change="syncVariationRows()"', $html);
        $this->assertStringContainsString('syncVariationRows()', $html);
        $this->assertStringContainsString('blankVariation(values)', $html);
    }

    /**
 * An attribute chosen in the edit form has no product_attribute_id until the
 * save happens, so its combinations post a positional key the service has to
 * resolve. Without that, picking a second attribute produced nothing.
 */
public function test_combinations_post_a_positional_key_for_an_unsaved_attribute()
{
    $product = $this->variableProduct();

    $html = $this->actingAs($this->admin())
        ->get(route('admin.products.edit', $product))
        ->assertOk()
        ->getContent();

    // Slots are built from the live attribute rows, not only the saved ones.
    $this->assertStringContainsString('variationSlots()', $html);
    $this->assertStringContainsString("'p' + index", $html);
    $this->assertStringNotContainsString('this.variationOptions.filter(a => a.is_variation)', $html);
}

public function test_the_service_resolves_a_positional_variation_key()
{
    $product = $this->variableProduct();

    $color = Attribute::create(['name' => 'Colour', 'type' => Attribute::TYPE_COLOR]);
    $pink = $color->values()->create(['name' => 'Pink', 'color_code' => '#ffc0cb']);

    $this->actingAs($this->admin())->put(route('admin.products.update', $product), [
        'name' => 'Attribute Tee',
        'product_type' => 'variable',
        'regular_price' => '500',
        'attributes' => [
            // The already-saved Size keeps its product_attribute_id key.
            ['attribute_id' => $product->productAttributes->first()->id, 'value_ids' => [1, 2], 'is_visible' => '1', 'is_variation' => '1'],
            // Colour is being added in this session, so it posts as "p1".
            ['attribute_id' => $color->id, 'value_ids' => [$pink->id], 'is_visible' => '1', 'is_variation' => '1'],
        ],
        'variations' => [
            ['values' => [$product->productAttributes->first()->id => 1, 'p1' => $pink->id], 'regular_price' => '500'],
            ['values' => [$product->productAttributes->first()->id => 2, 'p1' => $pink->id], 'regular_price' => '500'],
        ],
    ])->assertRedirect();

    // Both combinations must survive, resolved back to real product attributes.
    $this->assertSame(2, $product->variations()->count());
    $this->assertTrue($product->variations()->get()->every(fn ($v) => $v->attributeValues->count() === 2));
}

public function test_the_variation_table_starts_collapsed()
    {
        $product = $this->variableProduct();

        $html = $this->actingAs($this->admin())
            ->get(route('admin.products.edit', $product))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('variationsOpen: false', $html);
        $this->assertStringContainsString('id="variations-panel"', $html);
        $this->assertStringContainsString('x-show="variationsOpen"', $html);
    }

    public function test_a_rejected_save_reopens_the_variation_table()
    {
        $product = $this->variableProduct();

        $this->actingAs($this->admin())
            ->from(route('admin.products.edit', $product))
            ->put(route('admin.products.update', $product), [
                'name' => 'Attribute Tee',
                'product_type' => 'variable',
                'regular_price' => '500',
                'variations' => [
                    ['regular_price' => '-5'],
                ],
            ])
            ->assertRedirect(route('admin.products.edit', $product))
            ->assertSessionHasErrors('variations.0.regular_price');

        $html = $this->actingAs($this->admin())
            ->get(route('admin.products.edit', $product))
            ->assertOk()
            ->getContent();

        // Hiding the rows that failed validation would leave the admin with a
        // save button that silently does nothing.
        $this->assertStringContainsString('variationsOpen: true', $html);
    }
}