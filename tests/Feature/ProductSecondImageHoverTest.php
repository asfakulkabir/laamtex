<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductSecondImageHoverTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
    }

    private function product(array $attributes = []): Product
    {
        return Product::create(array_merge([
            'user_id' => $this->admin()->id,
            'name' => 'Hover Kurta',
            'slug' => 'hover-kurta',
            'product_type' => 'simple',
            'regular_price' => '1200.00',
            'stock_quantity' => 5,
            'is_active' => true,
        ], $attributes));
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function withImages(Product $product, array $rows): Product
    {
        foreach ($rows as $row) {
            $product->images()->create($row + ['order' => 0]);
        }

        return $product->refresh();
    }

    /**
     * The edit form always resubmits the existing gallery rows, and the
     * controller only re-reads the featured/second marks from that payload.
     *
     * @return array<string, mixed>
     */
    private function updatePayload(Product $product, array $extra = []): array
    {
        $existing = [];

        foreach ($product->images as $index => $image) {
            $existing[$index] = [
                'id' => $image->id,
                'name' => $image->name,
                'alt_text' => $image->alt_text,
                'order' => $image->order,
            ];
        }

        return array_merge([
            'name' => $product->name,
            'product_type' => 'simple',
            'regular_price' => '1200',
            'stock_quantity' => 5,
            'existing_images' => $existing,
        ], $extra);
    }

    // -------------------------------------------------------------------
    // Resolving the hover image
    // -------------------------------------------------------------------

    public function test_no_hover_image_when_the_product_has_a_single_image(): void
    {
        $product = $this->withImages($this->product(), [
            ['image' => 'product_images/only.png', 'is_featured' => true],
        ]);

        $this->assertNull($product->hoverImage());
    }

    public function test_no_hover_image_when_the_product_has_no_images(): void
    {
        $this->assertNull($this->product()->hoverImage());
    }

    public function test_second_image_in_gallery_order_is_used_when_none_is_marked(): void
    {
        $product = $this->withImages($this->product(), [
            ['image' => 'product_images/first.png', 'is_featured' => true],
            ['image' => 'product_images/second.png'],
            ['image' => 'product_images/third.png'],
        ]);

        $this->assertSame('product_images/second.png', $product->hoverImage()->image);
    }

    public function test_marked_second_image_wins_over_gallery_order(): void
    {
        $product = $this->withImages($this->product(), [
            ['image' => 'product_images/first.png', 'is_featured' => true],
            ['image' => 'product_images/second.png'],
            ['image' => 'product_images/third.png', 'is_secondary' => true],
        ]);

        $this->assertSame('product_images/third.png', $product->hoverImage()->image);
    }

    public function test_hover_image_is_never_the_main_image(): void
    {
        $product = $this->withImages($this->product(), [
            ['image' => 'product_images/first.png', 'is_featured' => true, 'is_secondary' => true],
            ['image' => 'product_images/second.png'],
        ]);

        $this->assertSame('product_images/first.png', $product->mainImage()->image);
        $this->assertSame('product_images/second.png', $product->hoverImage()->image);
    }

    public function test_main_image_falls_back_to_the_first_gallery_entry(): void
    {
        $product = $this->withImages($this->product(), [
            ['image' => 'product_images/first.png'],
            ['image' => 'product_images/second.png'],
        ]);

        $this->assertSame('product_images/first.png', $product->mainImage()->image);
    }

    public function test_is_secondary_is_cast_to_a_boolean(): void
    {
        $image = ProductImage::create([
            'product_id' => $this->product()->id,
            'image' => 'product_images/cast.png',
        ]);

        $this->assertFalse($image->fresh()->is_secondary);
        $this->assertSame(0, ProductImage::where('is_secondary', true)->count());
    }

    // -------------------------------------------------------------------
    // Storefront card
    // -------------------------------------------------------------------

    public function test_shop_card_renders_the_second_image_for_hover(): void
    {
        Storage::fake('public');

        $product = $this->withImages($this->product(), [
            ['image' => 'product_images/first.png', 'is_featured' => true],
            ['image' => 'product_images/second.png'],
        ]);

        $html = $this->get(route('shop'))->assertOk()->getContent();

        $this->assertStringContainsString(Storage::url('product_images/first.png'), $html);
        $this->assertStringContainsString(Storage::url('product_images/second.png'), $html);
        $this->assertStringContainsString('group-hover:opacity-100', $html);

        $this->assertNotSame(
            $product->mainImage()->image,
            $product->hoverImage()->image
        );
    }

    public function test_shop_card_renders_no_hover_layer_for_a_single_image_product(): void
    {
        Storage::fake('public');

        $this->withImages($this->product(), [
            ['image' => 'product_images/only.png', 'is_featured' => true],
        ]);

        $html = $this->get(route('shop'))->assertOk()->getContent();

        $this->assertStringContainsString(Storage::url('product_images/only.png'), $html);
        $this->assertStringNotContainsString('group-hover:opacity-100', $html);
    }

    // -------------------------------------------------------------------
    // Admin: create
    // -------------------------------------------------------------------

    public function test_second_image_can_be_marked_on_creation(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())->post(route('admin.products.store'), [
            'name' => 'Marked Kurta',
            'product_type' => 'simple',
            'regular_price' => '1500',
            'stock_quantity' => 3,
            'images' => [
                UploadedFile::fake()->image('front.png'),
                UploadedFile::fake()->image('back.png'),
                UploadedFile::fake()->image('side.png'),
            ],
            'image_names' => ['front', 'back', 'side'],
            'image_featured_index' => 0,
            'image_secondary_index' => 2,
        ])->assertRedirect(route('admin.products.index'));

        $product = Product::firstWhere('name', 'Marked Kurta');

        $this->assertSame(1, $product->images()->where('is_featured', true)->count());
        $this->assertSame(1, $product->images()->where('is_secondary', true)->count());
        $this->assertSame('side', $product->hoverImage()->name);
    }

    public function test_the_same_image_cannot_be_both_main_and_second_on_creation(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())->post(route('admin.products.store'), [
            'name' => 'Clashing Marks',
            'product_type' => 'simple',
            'regular_price' => '1500',
            'stock_quantity' => 3,
            'images' => [
                UploadedFile::fake()->image('front.png'),
                UploadedFile::fake()->image('back.png'),
            ],
            'image_names' => ['front', 'back'],
            'image_featured_index' => 0,
            'image_secondary_index' => 0,
        ])->assertRedirect(route('admin.products.index'));

        $product = Product::firstWhere('name', 'Clashing Marks');

        $this->assertSame(1, $product->images()->where('is_featured', true)->count());
        $this->assertSame(0, $product->images()->where('is_secondary', true)->count());
        $this->assertSame('back', $product->hoverImage()->name);
    }

    // -------------------------------------------------------------------
    // Admin: update
    // -------------------------------------------------------------------

    public function test_an_existing_image_can_be_marked_as_the_second_image(): void
    {
        $product = $this->withImages($this->product(), [
            ['image' => 'product_images/front.png', 'name' => 'front', 'is_featured' => true],
            ['image' => 'product_images/back.png', 'name' => 'back'],
            ['image' => 'product_images/side.png', 'name' => 'side'],
        ]);

        $side = $product->images()->where('name', 'side')->first();

        $this->actingAs($this->admin())->put(route('admin.products.update', $product), $this->updatePayload($product, [
            'new_image_featured_temp' => 'existing_' . $product->images()->where('name', 'front')->first()->id,
            'new_image_secondary_temp' => 'existing_' . $side->id,
        ]))->assertRedirect(route('admin.products.index'));

        $product->refresh();

        $this->assertSame('side', $product->hoverImage()->name);
        $this->assertSame(1, $product->images()->where('is_secondary', true)->count());
    }

    public function test_choosing_a_new_upload_as_the_second_image_clears_the_previous_mark(): void
    {
        Storage::fake('public');

        $product = $this->withImages($this->product(), [
            ['image' => 'product_images/front.png', 'name' => 'front', 'is_featured' => true],
            ['image' => 'product_images/back.png', 'name' => 'back', 'is_secondary' => true],
        ]);

        $this->actingAs($this->admin())->put(route('admin.products.update', $product), $this->updatePayload($product, [
            'new_image_featured_temp' => 'existing_' . $product->images()->where('name', 'front')->first()->id,
            'new_image_secondary_temp' => 'new_0',
            'new_images' => [UploadedFile::fake()->image('detail.png')],
            'new_images_names' => ['detail'],
        ]))->assertRedirect(route('admin.products.index'));

        $product->refresh();

        $this->assertSame(1, $product->images()->where('is_secondary', true)->count());
        $this->assertSame('detail', $product->hoverImage()->name);
    }

    public function test_promoting_another_image_to_main_clears_the_old_main_mark(): void
    {
        $product = $this->withImages($this->product(), [
            ['image' => 'product_images/front.png', 'name' => 'front', 'is_featured' => true],
            ['image' => 'product_images/back.png', 'name' => 'back'],
        ]);

        $backId = $product->images()->where('name', 'back')->first()->id;

        $this->actingAs($this->admin())->put(route('admin.products.update', $product), $this->updatePayload($product, [
            'new_image_featured_temp' => 'existing_' . $backId,
        ]))->assertRedirect(route('admin.products.index'));

        $product->refresh();

        $this->assertSame(1, $product->images()->where('is_featured', true)->count());
        $this->assertSame('back', $product->mainImage()->name);
        $this->assertSame('front', $product->hoverImage()->name);
    }

    public function test_deleting_the_second_image_leaves_the_product_without_a_hover_image(): void
    {
        $product = $this->withImages($this->product(), [
            ['image' => 'product_images/front.png', 'name' => 'front', 'is_featured' => true],
            ['image' => 'product_images/back.png', 'name' => 'back'],
        ]);

        $this->actingAs($this->admin())->put(route('admin.products.update', $product), $this->updatePayload($product, [
            'delete_images' => [$product->images()->where('name', 'back')->first()->id],
        ]))->assertRedirect(route('admin.products.index'));

        $product->refresh();

        $this->assertCount(1, $product->images);
        $this->assertNull($product->hoverImage());
    }
}
