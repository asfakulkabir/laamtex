<?php

namespace Tests\Feature;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * WebP images and the server's upload ceilings, which are the two reasons a
 * variation's price and image appear not to save.
 */
class VariationUploadLimitsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
    }

    /** @return array{0: Attribute, 1: AttributeValue} */
    private function color(): array
    {
        $color = Attribute::create(['name' => 'Color', 'type' => Attribute::TYPE_COLOR]);

        return [$color, $color->values()->create(['name' => 'Pink', 'color_code' => '#ffc0cb'])];
    }

    /** A genuine WebP, built the way a browser would send one. */
    private function webp(string $name = 'pink.webp'): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'webp').'.webp';
        $image = imagecreatetruecolor(120, 120);
        imagefill($image, 0, 0, imagecolorallocate($image, 200, 30, 60));
        imagewebp($image, $path, 80);
        imagedestroy($image);

        $file = UploadedFile::fake()->createWithContent($name, file_get_contents($path));
        unlink($path);

        return $file;
    }

    /** Convert a php.ini shorthand size (e.g. "64M") to bytes. */
    private function toBytes(string $size): int
    {
        $value = (int) $size;

        return match (strtolower(substr($size, -1))) {
            'g' => $value * 1024 * 1024 * 1024,
            'm' => $value * 1024 * 1024,
            'k' => $value * 1024,
            default => $value,
        };
    }

    public function test_a_webp_variation_image_and_price_save_on_add()
    {
        Storage::fake('public');
        [$color, $pink] = $this->color();

        $this->actingAs($this->admin())->post(route('admin.products.store'), [
            'name' => 'Webp Add',
            'product_type' => 'variable',
            'regular_price' => '100.00',
            'is_active' => '1',
            'attributes' => [[
                'attribute_id' => $color->id,
                'value_ids' => [$pink->id],
                'is_visible' => '1',
                'is_variation' => '1',
            ]],
            'variations' => [[
                'values' => [0 => $pink->id],
                'regular_price' => '250',
                'sale_price' => '200',
                'manage_stock' => '1',
                'stock_quantity' => 7,
                'stock_status' => 'instock',
                'status' => 'publish',
                'image' => $this->webp(),
            ]],
        ])->assertSessionHasNoErrors();

        $variation = Product::where('name', 'Webp Add')->firstOrFail()->variations()->firstOrFail();

        $this->assertEquals('250.00', $variation->regular_price);
        $this->assertEquals('200.00', $variation->sale_price);
        $this->assertNotNull($variation->image);
        $this->assertStringEndsWith('.webp', $variation->image);
        Storage::disk('public')->assertExists($variation->image);
    }

    public function test_an_oversized_submission_explains_itself_instead_of_failing_silently()
    {
        [$color, $pink] = $this->color();

        $perImage = ini_get('upload_max_filesize') ?: '2M';
        $perRequest = ini_get('post_max_size') ?: '8M';

        $this->actingAs($this->admin())
            ->from(route('admin.products.create'))
            ->withServerVariables(['CONTENT_LENGTH' => (string) ($this->toBytes($perRequest) + 1)])
            ->post(route('admin.products.store'), [
                'name' => 'Too Big',
                'product_type' => 'variable',
                'regular_price' => '100.00',
                'is_active' => '1',
                'attributes' => [[
                    'attribute_id' => $color->id,
                    'value_ids' => [$pink->id],
                    'is_visible' => '1',
                    'is_variation' => '1',
                ]],
                'variations' => [[
                    'values' => [0 => $pink->id],
                    'regular_price' => '250',
                    'status' => 'publish',
                ]],
            ])
            ->assertRedirect(route('admin.products.create'))
            ->assertSessionHasErrors('images');

        $message = session('errors')->first('images');

        $this->assertStringContainsString('too large', $message);
        $this->assertStringContainsString($perImage, $message);
        $this->assertStringContainsString($perRequest, $message);
        $this->assertStringContainsString('post_max_size', $message);

        // Nothing was half created.
        $this->assertSame(0, Product::count());
    }

    public function test_the_add_page_states_the_upload_limits()
    {
        $html = $this->actingAs($this->admin())->get(route('admin.products.create'))->getContent();

        $this->assertStringContainsString('Images: up to', $html);
        $this->assertStringContainsString((ini_get('upload_max_filesize') ?: '2M'), $html);
        $this->assertStringContainsString((ini_get('post_max_size') ?: '8M'), $html);
    }

    public function test_the_edit_page_states_the_upload_limits()
    {
        $admin = $this->admin();

        $color = Attribute::create(['name' => 'Color', 'type' => Attribute::TYPE_COLOR]);
        $pink = $color->values()->create(['name' => 'Pink']);

        $product = Product::create([
            'user_id' => $admin->id,
            'name' => 'Limits',
            'slug' => 'limits',
            'product_type' => 'variable',
            'regular_price' => '100.00',
            'is_active' => true,
        ]);
        $productAttribute = $product->productAttributes()->create([
            'attribute_id' => $color->id, 'position' => 1, 'is_visible' => true, 'is_variation' => true,
        ]);
        $productAttribute->values()->attach([$pink->id]);

        $html = $this->actingAs($admin)->get(route('admin.products.edit', $product))->getContent();

        $this->assertStringContainsString('Images: up to', $html);
        $this->assertStringContainsString((ini_get('post_max_size') ?: '8M'), $html);
    }

    public function test_more_images_than_the_server_allows_is_reported()
    {
        Storage::fake('public');
        [$color, $pink] = $this->color();

        $max = (int) (ini_get('max_file_uploads') ?: 20);

        // One variation plus enough gallery images to pass the file count cap.
        $gallery = [];
        for ($i = 0; $i < $max; $i++) {
            $gallery[] = $this->webp("gallery-{$i}.webp");
        }

        $this->actingAs($this->admin())->post(route('admin.products.store'), [
            'name' => 'Many Images',
            'product_type' => 'variable',
            'regular_price' => '100.00',
            'is_active' => '1',
            'attributes' => [[
                'attribute_id' => $color->id,
                'value_ids' => [$pink->id],
                'is_visible' => '1',
                'is_variation' => '1',
            ]],
            'variations' => [[
                'values' => [0 => $pink->id],
                'regular_price' => '100',
                'status' => 'publish',
                'image' => $this->webp('variation.webp'),
            ]],
            'images' => $gallery,
        ])->assertSessionHasNoErrors();

        $this->assertStringContainsString('max_file_uploads', (string) session('error'));
    }
}
