<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\SizeChart;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SizeChartTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        return User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
    }

    private function createSizeChart(string $title = 'Unisex T-Shirt'): SizeChart
    {
        return SizeChart::create([
            'title' => $title,
            'image' => 'size_charts/chart.jpg',
            'is_active' => true,
            'sort_order' => 1,
        ]);
    }

    private function createProduct(array $overrides = []): Product
    {
        return Product::create(array_merge([
            'name' => 'Test Tee',
            'product_type' => 'simple',
            'regular_price' => 1000,
            'stock_quantity' => 5,
            'is_active' => true,
        ], $overrides));
    }

    public function test_super_admin_can_add_a_size_chart()
    {
        Storage::fake('public');
        $admin = $this->superAdmin();

        $this->actingAs($admin)->get(route('admin.size-charts.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.size-charts.create'))->assertOk();

        $this->actingAs($admin)->post(route('admin.size-charts.store'), [
            'title' => 'Unisex T-Shirt',
            'image' => UploadedFile::fake()->image('chart.jpg', 800, 1000),
        ])->assertRedirect(route('admin.size-charts.index'));

        $chart = SizeChart::first();
        $this->assertNotNull($chart);
        $this->assertSame('Unisex T-Shirt', $chart->title);
        $this->assertTrue($chart->is_active);
        $this->assertSame(800, $chart->width);
        $this->assertSame(1000, $chart->height);
        Storage::disk('public')->assertExists($chart->image);
    }

    public function test_title_and_image_are_required()
    {
        Storage::fake('public');
        $admin = $this->superAdmin();

        $this->actingAs($admin)->post(route('admin.size-charts.store'), [])
            ->assertSessionHasErrors(['title', 'image']);

        $this->assertSame(0, SizeChart::count());
    }

    public function test_non_image_files_are_rejected()
    {
        Storage::fake('public');
        $admin = $this->superAdmin();

        $this->actingAs($admin)->post(route('admin.size-charts.store'), [
            'title' => 'Bad',
            'image' => UploadedFile::fake()->create('malicious.php', 10, 'application/x-php'),
        ])->assertSessionHasErrors('image');

        $this->assertSame(0, SizeChart::count());
    }

    public function test_title_can_be_updated_without_replacing_the_image()
    {
        Storage::fake('public');
        $admin = $this->superAdmin();

        $this->actingAs($admin)->post(route('admin.size-charts.store'), [
            'title' => 'Old Title',
            'image' => UploadedFile::fake()->image('chart.jpg'),
        ]);

        $chart = SizeChart::first();
        $originalPath = $chart->image;

        $this->actingAs($admin)->put(route('admin.size-charts.update', $chart), [
            'title' => 'New Title',
        ])->assertRedirect(route('admin.size-charts.index'));

        $chart->refresh();
        $this->assertSame('New Title', $chart->title);
        $this->assertSame($originalPath, $chart->image);
        Storage::disk('public')->assertExists($originalPath);
    }

    public function test_replacing_the_image_removes_the_old_file()
    {
        Storage::fake('public');
        $admin = $this->superAdmin();

        $this->actingAs($admin)->post(route('admin.size-charts.store'), [
            'title' => 'Chart',
            'image' => UploadedFile::fake()->image('first.jpg'),
        ]);

        $chart = SizeChart::first();
        $oldPath = $chart->image;

        $this->actingAs($admin)->put(route('admin.size-charts.update', $chart), [
            'title' => 'Chart',
            'image' => UploadedFile::fake()->image('second.jpg'),
        ]);

        $chart->refresh();
        $this->assertNotSame($oldPath, $chart->image);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($chart->image);
    }

    public function test_deleting_a_size_chart_removes_the_file_and_detaches_products()
    {
        Storage::fake('public');
        $admin = $this->superAdmin();

        $this->actingAs($admin)->post(route('admin.size-charts.store'), [
            'title' => 'Chart',
            'image' => UploadedFile::fake()->image('chart.jpg'),
        ]);

        $chart = SizeChart::first();
        $path = $chart->image;

        $product = $this->createProduct(['size_chart_id' => $chart->id]);
        $this->assertTrue($product->sizeChart->is($chart));

        $this->actingAs($admin)->delete(route('admin.size-charts.destroy', $chart))
            ->assertRedirect(route('admin.size-charts.index'));

        Storage::disk('public')->assertMissing($path);

        // The product survives but no longer points at a missing row.
        $product->refresh();
        $this->assertNull($product->size_chart_id);
        $this->assertNull($product->sizeChart);
    }

    public function test_toggle_flips_the_active_flag()
    {
        Storage::fake('public');
        $admin = $this->superAdmin();
        $chart = $this->createSizeChart();

        $this->actingAs($admin)->post(route('admin.size-charts.toggle', $chart))->assertRedirect();
        $this->assertFalse($chart->refresh()->is_active);

        $this->actingAs($admin)->post(route('admin.size-charts.toggle', $chart))->assertRedirect();
        $this->assertTrue($chart->refresh()->is_active);
    }

    public function test_moderators_cannot_manage_size_charts()
    {
        Storage::fake('public');
        $moderator = User::factory()->create(['role' => User::ROLE_MODERATOR]);

        // Moderators are bounced to the orders page by the super_admin guard.
        $this->actingAs($moderator)->get(route('admin.size-charts.index'))
            ->assertRedirect(route('admin.orders.index'));
        $this->actingAs($moderator)->post(route('admin.size-charts.store'), [
            'title' => 'Nope',
            'image' => UploadedFile::fake()->image('chart.jpg'),
        ])->assertRedirect(route('admin.orders.index'));

        $this->assertSame(0, SizeChart::count());
    }

    public function test_guests_are_redirected_to_login()
    {
        $this->get(route('admin.size-charts.index'))->assertRedirect(route('admin.login'));
    }

    public function test_product_form_offers_a_none_option_and_the_selected_chart()
    {
        Storage::fake('public');
        $admin = $this->superAdmin();
        $chart = $this->createSizeChart('Chart A');

        $this->actingAs($admin)->get(route('admin.products.create'))
            ->assertOk()
            ->assertSee('Chart A')
            ->assertSee('None');

        $product = $this->createProduct(['size_chart_id' => $chart->id]);

        $this->actingAs($admin)->get(route('admin.products.edit', $product))
            ->assertOk()
            ->assertSee('Chart A');
    }

    public function test_creating_a_product_can_attach_a_size_chart()
    {
        Storage::fake('public');
        $admin = $this->superAdmin();
        $chart = $this->createSizeChart();

        $this->actingAs($admin)->post(route('admin.products.store'), [
            'name' => 'New Tee',
            'product_type' => 'simple',
            'regular_price' => 1200,
            'stock_quantity' => 4,
            'size_chart_id' => $chart->id,
        ])->assertRedirect(route('admin.products.index'));

        $product = Product::where('slug', 'new-tee')->firstOrFail();
        $this->assertSame($chart->id, $product->size_chart_id);
    }

    public function test_creating_a_product_without_a_size_chart_leaves_it_null()
    {
        Storage::fake('public');
        $admin = $this->superAdmin();

        $this->actingAs($admin)->post(route('admin.products.store'), [
            'name' => 'Plain Tee',
            'product_type' => 'simple',
            'regular_price' => 900,
            'stock_quantity' => 4,
            'size_chart_id' => '',
        ])->assertRedirect(route('admin.products.index'));

        $product = Product::where('slug', 'plain-tee')->firstOrFail();
        $this->assertNull($product->size_chart_id);
    }

    public function test_updating_a_product_can_change_or_clear_the_size_chart()
    {
        Storage::fake('public');
        $admin = $this->superAdmin();
        $chartA = $this->createSizeChart('Chart A');
        $chartB = SizeChart::create([
            'title' => 'Chart B',
            'image' => 'size_charts/b.jpg',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $product = $this->createProduct(['size_chart_id' => $chartA->id]);

        $payload = [
            'name' => $product->name,
            'product_type' => 'simple',
            'regular_price' => 1000,
            'stock_quantity' => 5,
        ];

        $this->actingAs($admin)->put(route('admin.products.update', $product), $payload + [
            'size_chart_id' => $chartB->id,
        ])->assertRedirect(route('admin.products.index'));

        $this->assertSame($chartB->id, $product->refresh()->size_chart_id);

        // Picking "None" must clear it rather than leave the old chart in place.
        $this->actingAs($admin)->put(route('admin.products.update', $product), $payload + [
            'size_chart_id' => '',
        ])->assertRedirect(route('admin.products.index'));

        $this->assertNull($product->refresh()->size_chart_id);
    }

    public function test_product_rejects_a_size_chart_id_that_does_not_exist()
    {
        Storage::fake('public');
        $admin = $this->superAdmin();

        $this->actingAs($admin)->post(route('admin.products.store'), [
            'name' => 'Ghost Tee',
            'product_type' => 'simple',
            'regular_price' => 500,
            'stock_quantity' => 2,
            'size_chart_id' => 9999,
        ])->assertSessionHasErrors('size_chart_id');

        $this->assertSame(0, Product::count());
    }

    public function test_product_page_shows_the_size_chart_link_when_one_is_attached()
    {
        Storage::fake('public');
        $admin = $this->superAdmin();
        $chart = $this->createSizeChart('Cotton Tee Measurements');

        $product = $this->createProduct(['name' => 'Cotton Tee', 'size_chart_id' => $chart->id]);

        $this->actingAs($admin)->get(route('product.detail', $product->slug))
            ->assertOk()
            // The link reads as a fixed label, so the admin's title stays internal.
            ->assertSee('Size Chart')
            ->assertSee('@click="sizeChartOpen = ! sizeChartOpen"', false)
            ->assertSee('x-show="sizeChartOpen"', false)
            // The title is not shown to shoppers.
            ->assertDontSee('>Cotton Tee Measurements<', false);
    }

    public function test_product_page_hides_the_size_chart_link_when_none_is_attached()
    {
        $product = $this->createProduct(['name' => 'No Chart Tee']);

        $this->get(route('product.detail', $product->slug))
            ->assertOk()
            ->assertDontSee('@click="sizeChartOpen = ! sizeChartOpen"', false)
            ->assertDontSee('x-show="sizeChartOpen"', false);
    }
}
