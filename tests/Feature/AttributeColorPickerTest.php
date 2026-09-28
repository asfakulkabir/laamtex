<?php
namespace Tests\Feature;

use App\Models\{Attribute, AttributeValue, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttributeColorPickerTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
    }

    private function colorAttribute(): Attribute
    {
        return Attribute::create(['name' => 'Color', 'slug' => 'color', 'type' => 'color']);
    }

    public function test_attribute_page_renders_a_colour_picker(): void
    {
        $admin = $this->admin();
        $attribute = $this->colorAttribute();
        $attribute->values()->create(['name' => 'Navy', 'slug' => 'navy', 'color_code' => '#000080']);

        $html = $this->actingAs($admin)->get(route('admin.attributes.index'))
            ->assertOk()
            ->getContent();

        // Both the create form and the existing value row get a picker.
        $this->assertSame(2, substr_count($html, 'type="color"'));
        // The text box is bound for editing but is not what gets submitted.
        $this->assertSame(2, substr_count($html, 'name="color_code"'));
        // Existing values are seeded into the picker.
        $this->assertStringContainsString('#000080', $html);
    }

    public function test_short_and_bare_hex_values_are_normalised_on_render(): void
    {
        $admin = $this->admin();
        $attribute = $this->colorAttribute();

        $attribute->values()->create(['name' => 'Red', 'slug' => 'red', 'color_code' => '#f00']);
        $attribute->values()->create(['name' => 'Blue', 'slug' => 'blue', 'color_code' => '00ff00']);

        $html = $this->actingAs($admin)->get(route('admin.attributes.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('#ff0000', $html);
        $this->assertStringContainsString('#00ff00', $html);
    }

    public function test_non_colour_attributes_do_not_render_a_picker(): void
    {
        $admin = $this->admin();
        $attribute = Attribute::create(['name' => 'Size', 'slug' => 'size', 'type' => 'text']);
        $attribute->values()->create(['name' => 'Small', 'slug' => 'small']);

        $html = $this->actingAs($admin)->get(route('admin.attributes.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString('type="color"', $html);
        $this->assertStringNotContainsString('name="color_code"', $html);
    }

    public function test_a_colour_value_can_be_saved(): void
    {
        $admin = $this->admin();
        $attribute = $this->colorAttribute();

        $this->actingAs($admin)->post(route('admin.attributes.values.store', $attribute->id), [
            'name' => 'Emerald',
            'color_code' => '#50c878',
        ])->assertRedirect();

        $this->assertDatabaseHas('attribute_values', [
            'attribute_id' => $attribute->id,
            'name' => 'Emerald',
            'color_code' => '#50c878',
        ]);
    }
}
