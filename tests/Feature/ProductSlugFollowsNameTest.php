<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * A renamed product must publish under a matching slug, otherwise the URL keeps
 * advertising the old title.
 */
class ProductSlugFollowsNameTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
    }

    private function product(string $name = 'Original Tee'): Product
    {
        return Product::create([
            'name' => $name,
            'slug' => 'original-tee',
            'product_type' => 'simple',
            'regular_price' => 500,
            'is_active' => true,
        ]);
    }

    public function test_renaming_a_product_updates_its_slug()
    {
        $product = $this->product();

        $this->actingAs($this->admin())->put(route('admin.products.update', $product), [
            'name' => 'Renamed Hoodie',
            'product_type' => 'simple',
            'regular_price' => '500',
            'stock_quantity' => '5',
        ])->assertRedirect();

        $this->assertSame('renamed-hoodie', $product->fresh()->slug);
    }

    public function test_saving_without_renaming_leaves_the_slug_alone()
    {
        $product = $this->product();

        $this->actingAs($this->admin())->put(route('admin.products.update', $product), [
            'name' => 'Original Tee',
            'product_type' => 'simple',
            'regular_price' => '600',
            'stock_quantity' => '5',
        ])->assertRedirect();

        // Re-saving must never rewrite a slug that is already published.
        $this->assertSame('original-tee', $product->fresh()->slug);
    }

    public function test_renaming_to_a_title_used_elsewhere_gets_a_unique_slug()
    {
        Product::create([
            'name' => 'Taken Name',
            'slug' => 'taken-name',
            'product_type' => 'simple',
            'regular_price' => 100,
            'is_active' => true,
        ]);

        $product = $this->product();

        $this->actingAs($this->admin())->put(route('admin.products.update', $product), [
            'name' => 'Taken Name',
            'product_type' => 'simple',
            'regular_price' => '500',
            'stock_quantity' => '5',
        ])->assertRedirect();

        $this->assertSame('taken-name-1', $product->fresh()->slug);
    }

    public function test_a_title_with_no_slug_characters_keeps_the_existing_slug()
    {
        $product = $this->product();

        $product->update(['name' => '★★★']);

        // Str::slug returns an empty string here, which would publish a broken URL.
        $this->assertSame('original-tee', $product->fresh()->slug);
    }

    public function test_the_edit_page_previews_the_slug()
    {
        $product = $this->product();

        $this->actingAs($this->admin())
            ->get(route('admin.products.edit', $product))
            ->assertOk()
            ->assertSee('slugPreview', false)
            ->assertSee('/product/', false);
    }
}