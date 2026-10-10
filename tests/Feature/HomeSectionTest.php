<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\HomeSection;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeSectionTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        return User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
    }

    private function category(string $name, ?Category $parent = null): Category
    {
        return Category::create([
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name),
            'parent_id' => $parent?->id,
        ]);
    }

    private function product(string $name, array $categories = [], array $overrides = []): Product
    {
        $product = Product::create(array_merge([
            'name' => $name,
            'product_type' => 'simple',
            'regular_price' => 1000,
            'stock_quantity' => 5,
            'is_active' => true,
        ], $overrides));

        if ($categories !== []) {
            $product->categories()->sync(collect($categories)->map(fn ($c) => $c->id)->all());
        }

        return $product;
    }

    public function test_super_admin_can_add_a_section()
    {
        $admin = $this->superAdmin();
        $category = $this->category('Shirts');

        $this->actingAs($admin)->get(route('admin.home-sections.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.home-sections.create'))->assertOk();

        $this->actingAs($admin)->post(route('admin.home-sections.store'), [
            'title' => 'New Shirts',
            'subtitle' => 'Fresh arrivals',
            'category_id' => $category->id,
            'layout' => 'slider',
            'product_limit' => 6,
            'is_active' => '1',
        ])->assertRedirect(route('admin.home-sections.index'));

        $section = HomeSection::first();
        $this->assertNotNull($section);
        $this->assertSame('New Shirts', $section->title);
        $this->assertSame($category->id, $section->category_id);
        $this->assertSame('slider', $section->layout);
        $this->assertSame(6, $section->product_limit);
        $this->assertTrue($section->is_active);
        $this->assertSame(1, $section->sort_order);
    }

    public function test_creating_a_section_requires_a_title_layout_and_limit()
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->post(route('admin.home-sections.store'), [])
            ->assertSessionHasErrors(['title', 'layout', 'product_limit']);

        $this->assertSame(0, HomeSection::count());
    }

    public function test_layout_must_be_slider_or_grid()
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->post(route('admin.home-sections.store'), [
            'title' => 'Bad layout',
            'layout' => 'carousel',
            'product_limit' => 4,
        ])->assertSessionHasErrors('layout');

        $this->assertSame(0, HomeSection::count());
    }

    public function test_product_limit_is_bounded()
    {
        $admin = $this->superAdmin();

        $payload = ['title' => 'Too many', 'layout' => 'grid'];

        $this->actingAs($admin)->post(route('admin.home-sections.store'), $payload + ['product_limit' => 0])
            ->assertSessionHasErrors('product_limit');

        $this->actingAs($admin)->post(route('admin.home-sections.store'), $payload + ['product_limit' => 999])
            ->assertSessionHasErrors('product_limit');

        $this->assertSame(0, HomeSection::count());
    }

    public function test_category_id_must_exist()
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->post(route('admin.home-sections.store'), [
            'title' => 'Ghost',
            'layout' => 'grid',
            'product_limit' => 4,
            'category_id' => 9999,
        ])->assertSessionHasErrors('category_id');

        $this->assertSame(0, HomeSection::count());
    }

    public function test_empty_category_means_all_categories()
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->post(route('admin.home-sections.store'), [
            'title' => 'Everything',
            'layout' => 'grid',
            'product_limit' => 4,
            'category_id' => '',
        ])->assertRedirect(route('admin.home-sections.index'));

        $this->assertNull(HomeSection::first()->category_id);
    }

    public function test_section_shows_only_its_categorys_products()
    {
        $shirts = $this->category('Shirts');
        $pants = $this->category('Pants');

        $this->product('Blue Shirt', [$shirts]);
        $this->product('Grey Pants', [$pants]);

        $section = HomeSection::create([
            'title' => 'Shirts',
            'category_id' => $shirts->id,
            'layout' => 'grid',
            'product_limit' => 10,
            'is_active' => true,
        ]);

        $names = $section->products()->pluck('name')->all();

        $this->assertSame(['Blue Shirt'], $names);
    }

    public function test_section_includes_products_from_child_categories()
    {
        $tops = $this->category('Tops');
        $tshirts = $this->category('T-Shirts', $tops);

        $this->product('Parent Level Tee', [$tops]);
        $this->product('Child Level Tee', [$tshirts]);

        $section = HomeSection::create([
            'title' => 'Tops',
            'category_id' => $tops->id,
            'layout' => 'grid',
            'product_limit' => 10,
            'is_active' => true,
        ]);

        $this->assertEqualsCanonicalizing(
            ['Parent Level Tee', 'Child Level Tee'],
            $section->products()->pluck('name')->all()
        );
    }

    public function test_section_excludes_inactive_products()
    {
        $category = $this->category('Shirts');
        $this->product('Visible', [$category]);
        $this->product('Hidden', [$category], ['is_active' => false]);

        $section = HomeSection::create([
            'title' => 'Shirts',
            'category_id' => $category->id,
            'layout' => 'grid',
            'product_limit' => 10,
            'is_active' => true,
        ]);

        $this->assertSame(['Visible'], $section->products()->pluck('name')->all());
    }

    public function test_section_respects_the_product_limit()
    {
        $category = $this->category('Shirts');

        foreach (range(1, 5) as $i) {
            $this->product("Shirt {$i}", [$category]);
        }

        $section = HomeSection::create([
            'title' => 'Shirts',
            'category_id' => $category->id,
            'layout' => 'grid',
            'product_limit' => 2,
            'is_active' => true,
        ]);

        $this->assertCount(2, $section->products());
    }

    public function test_home_page_renders_an_active_section_with_its_heading()
    {
        $category = $this->category('Shirts');
        $this->product('Blue Shirt', [$category]);

        HomeSection::create([
            'title' => 'Shirt Sale',
            'subtitle' => 'Up to 50% off',
            'category_id' => $category->id,
            'layout' => 'grid',
            'product_limit' => 4,
            'is_active' => true,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Shirt Sale')
            ->assertSee('Up to 50% off')
            ->assertSee('Blue Shirt');
    }

    public function test_home_page_renders_a_slider_section()
    {
        $category = $this->category('Shirts');
        $this->product('Blue Shirt', [$category]);

        HomeSection::create([
            'title' => 'Shirt Carousel',
            'category_id' => $category->id,
            'layout' => 'slider',
            'product_limit' => 4,
            'is_active' => true,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Shirt Carousel')
            // The carousel wiring has to be emitted for the slider to work.
            ->assertSee('x-data="featuredSlider()"', false)
            ->assertSee('Blue Shirt');
    }

    public function test_hidden_section_is_not_rendered_on_the_home_page()
    {
        $category = $this->category('Shirts');
        $this->product('Blue Shirt', [$category]);

        HomeSection::create([
            'title' => 'Hidden Section',
            'category_id' => $category->id,
            'layout' => 'grid',
            'product_limit' => 4,
            'is_active' => false,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Hidden Section');
    }

    public function test_section_with_no_matching_products_is_skipped()
    {
        $empty = $this->category('Empty');

        HomeSection::create([
            'title' => 'Nothing Here',
            'category_id' => $empty->id,
            'layout' => 'grid',
            'product_limit' => 4,
            'is_active' => true,
        ]);

        // Rendering an empty heading with nothing under it looks broken.
        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Nothing Here');
    }

    public function test_toggle_flips_the_active_flag()
    {
        $admin = $this->superAdmin();
        $section = HomeSection::create([
            'title' => 'Section',
            'layout' => 'grid',
            'product_limit' => 4,
            'is_active' => true,
        ]);

        $this->actingAs($admin)->post(route('admin.home-sections.toggle', $section))->assertRedirect();
        $this->assertFalse($section->refresh()->is_active);

        $this->actingAs($admin)->post(route('admin.home-sections.toggle', $section))->assertRedirect();
        $this->assertTrue($section->refresh()->is_active);
    }

    public function test_updating_a_section_replaces_its_settings()
    {
        $admin = $this->superAdmin();
        $category = $this->category('Shirts');

        $section = HomeSection::create([
            'title' => 'Original',
            'category_id' => null,
            'layout' => 'grid',
            'product_limit' => 4,
            'is_active' => true,
        ]);

        $this->actingAs($admin)->put(route('admin.home-sections.update', $section), [
            'title' => 'Updated',
            'category_id' => $category->id,
            'layout' => 'slider',
            'product_limit' => 12,
            'is_active' => '1',
        ])->assertRedirect(route('admin.home-sections.index'));

        $section->refresh();
        $this->assertSame('Updated', $section->title);
        $this->assertSame($category->id, $section->category_id);
        $this->assertSame('slider', $section->layout);
        $this->assertSame(12, $section->product_limit);
    }

    public function test_unchecking_the_active_box_hides_the_section()
    {
        $admin = $this->superAdmin();

        $section = HomeSection::create([
            'title' => 'Section',
            'layout' => 'grid',
            'product_limit' => 4,
            'is_active' => true,
        ]);

        $this->actingAs($admin)->put(route('admin.home-sections.update', $section), [
            'title' => 'Section',
            'layout' => 'grid',
            'product_limit' => 4,
        ])->assertRedirect(route('admin.home-sections.index'));

        $this->assertFalse($section->refresh()->is_active);
    }

    public function test_reorder_writes_the_new_sort_order()
    {
        $admin = $this->superAdmin();

        $first = HomeSection::create(['title' => 'First', 'layout' => 'grid', 'product_limit' => 4, 'is_active' => true, 'sort_order' => 1]);
        $second = HomeSection::create(['title' => 'Second', 'layout' => 'grid', 'product_limit' => 4, 'is_active' => true, 'sort_order' => 2]);

        $this->actingAs($admin)->post(route('admin.home-sections.reorder'), [
            'order' => [$second->id, $first->id],
        ])->assertOk();

        $this->assertSame(1, $second->refresh()->sort_order);
        $this->assertSame(2, $first->refresh()->sort_order);
    }

    public function test_deleting_a_section_removes_it()
    {
        $admin = $this->superAdmin();
        $section = HomeSection::create(['title' => 'Gone', 'layout' => 'grid', 'product_limit' => 4, 'is_active' => true]);

        $this->actingAs($admin)->delete(route('admin.home-sections.destroy', $section))
            ->assertRedirect(route('admin.home-sections.index'));

        $this->assertSame(0, HomeSection::count());
    }

    public function test_moderators_cannot_manage_home_sections()
    {
        $moderator = User::factory()->create(['role' => User::ROLE_MODERATOR]);

        $this->actingAs($moderator)->get(route('admin.home-sections.index'))
            ->assertRedirect(route('admin.orders.index'));

        $this->actingAs($moderator)->post(route('admin.home-sections.store'), [
            'title' => 'Nope',
            'layout' => 'grid',
            'product_limit' => 4,
        ])->assertRedirect(route('admin.orders.index'));

        $this->assertSame(0, HomeSection::count());
    }

    public function test_guests_are_redirected_to_login()
    {
        $this->get(route('admin.home-sections.index'))->assertRedirect(route('admin.login'));
    }
}
