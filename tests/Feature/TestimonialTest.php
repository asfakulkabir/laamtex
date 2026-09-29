<?php

namespace Tests\Feature;

use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TestimonialTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        return User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
    }

    public function test_super_admin_can_upload_a_testimonial_image()
    {
        Storage::fake('public');
        $admin = $this->superAdmin();

        $this->actingAs($admin)->get(route('admin.testimonials.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.testimonials.create'))->assertOk();

        $this->actingAs($admin)->post(route('admin.testimonials.store'), [
            'image' => UploadedFile::fake()->image('review.jpg', 800, 800),
        ])->assertRedirect(route('admin.testimonials.index'));

        $testimonial = Testimonial::first();
        $this->assertNotNull($testimonial);
        $this->assertTrue($testimonial->is_active);
        $this->assertSame(1, $testimonial->sort_order);
        Storage::disk('public')->assertExists($testimonial->image);
    }

    public function test_image_is_required()
    {
        Storage::fake('public');
        $admin = $this->superAdmin();

        $this->actingAs($admin)->post(route('admin.testimonials.store'), [])
            ->assertSessionHasErrors('image');

        $this->assertSame(0, Testimonial::count());
    }

    public function test_non_image_files_are_rejected()
    {
        Storage::fake('public');
        $admin = $this->superAdmin();

        $this->actingAs($admin)->post(route('admin.testimonials.store'), [
            'image' => UploadedFile::fake()->create('malicious.php', 10, 'application/x-php'),
        ])->assertSessionHasErrors('image');

        $this->assertSame(0, Testimonial::count());
    }

    public function test_replacing_the_image_removes_the_old_file()
    {
        Storage::fake('public');
        $admin = $this->superAdmin();

        $this->actingAs($admin)->post(route('admin.testimonials.store'), [
            'image' => UploadedFile::fake()->image('first.jpg'),
        ]);

        $testimonial = Testimonial::first();
        $oldPath = $testimonial->image;

        $this->actingAs($admin)->put(route('admin.testimonials.update', $testimonial), [
            'image' => UploadedFile::fake()->image('second.jpg'),
        ])->assertRedirect(route('admin.testimonials.index'));

        Storage::disk('public')->assertMissing($oldPath);
        $this->assertNotSame($oldPath, $testimonial->fresh()->image);
    }

    public function test_toggle_hides_and_shows_a_testimonial()
    {
        Storage::fake('public');
        $admin = $this->superAdmin();

        $this->actingAs($admin)->post(route('admin.testimonials.store'), [
            'image' => UploadedFile::fake()->image('a.jpg'),
        ]);
        $testimonial = Testimonial::first();

        $this->actingAs($admin)->post(route('admin.testimonials.toggle', $testimonial));
        $this->assertFalse($testimonial->fresh()->is_active);
        $this->assertSame(0, Testimonial::active()->count());

        $this->actingAs($admin)->post(route('admin.testimonials.toggle', $testimonial));
        $this->assertTrue($testimonial->fresh()->is_active);
        $this->assertSame(1, Testimonial::active()->count());
    }

    public function test_delete_removes_the_row_and_the_file()
    {
        Storage::fake('public');
        $admin = $this->superAdmin();

        $this->actingAs($admin)->post(route('admin.testimonials.store'), [
            'image' => UploadedFile::fake()->image('a.jpg'),
        ]);
        $testimonial = Testimonial::first();
        $path = $testimonial->image;

        $this->actingAs($admin)->delete(route('admin.testimonials.destroy', $testimonial))
            ->assertRedirect(route('admin.testimonials.index'));

        $this->assertSame(0, Testimonial::count());
        Storage::disk('public')->assertMissing($path);
    }

    public function test_reorder_persists_the_new_order()
    {
        Storage::fake('public');
        $admin = $this->superAdmin();

        foreach (['a', 'b', 'c'] as $name) {
            $this->actingAs($admin)->post(route('admin.testimonials.store'), [
                'image' => UploadedFile::fake()->image($name . '.jpg'),
            ]);
        }

        $ids = Testimonial::orderBy('sort_order')->pluck('id')->all();
        $reversed = array_reverse($ids);

        $this->actingAs($admin)->postJson(route('admin.testimonials.reorder'), ['order' => $reversed])
            ->assertOk();

        $this->assertSame($reversed, Testimonial::orderBy('sort_order')->pluck('id')->all());
    }

    public function test_moderator_cannot_manage_testimonials()
    {
        Storage::fake('public');
        $moderator = User::factory()->create(['role' => User::ROLE_MODERATOR]);

        $this->actingAs($moderator)->get(route('admin.testimonials.index'))
            ->assertRedirect(route('admin.orders.index'));

        $this->actingAs($moderator)->post(route('admin.testimonials.store'), [
            'image' => UploadedFile::fake()->image('a.jpg'),
        ])->assertRedirect(route('admin.orders.index'));

        $this->assertSame(0, Testimonial::count());
    }

    public function test_home_page_shows_only_active_testimonials()
    {
        Storage::fake('public');
        $admin = $this->superAdmin();

        foreach (range(1, 2) as $i) {
            $this->actingAs($admin)->post(route('admin.testimonials.store'), [
                'image' => UploadedFile::fake()->image("t{$i}.jpg"),
            ]);
        }
        $hidden = Testimonial::orderByDesc('id')->first();
        $hidden->update(['is_active' => false]);

        $response = $this->get(route('home'));
        $response->assertOk();
        $this->assertCount(1, $response->viewData('testimonials'));
        $response->assertSee('What Our Customers Say');
        $response->assertDontSee($hidden->image);
    }

    public function test_home_page_hides_the_section_when_there_are_none()
    {
        $response = $this->get(route('home'));
        $response->assertOk();
        $this->assertCount(0, $response->viewData('testimonials'));
        $response->assertDontSee('What Our Customers Say');
    }

    public function test_upload_records_the_image_dimensions_and_size()
    {
        Storage::fake('public');
        $admin = $this->superAdmin();

        $this->actingAs($admin)->post(route('admin.testimonials.store'), [
            'image' => UploadedFile::fake()->image('landscape.jpg', 1200, 675),
        ])->assertRedirect(route('admin.testimonials.index'));

        $testimonial = Testimonial::first();

        $this->assertSame(1200, $testimonial->width);
        $this->assertSame(675, $testimonial->height);
        $this->assertGreaterThan(0, $testimonial->file_size);
    }

    public function test_replacing_the_image_updates_the_recorded_dimensions()
    {
        Storage::fake('public');
        $admin = $this->superAdmin();

        $this->actingAs($admin)->post(route('admin.testimonials.store'), [
            'image' => UploadedFile::fake()->image('portrait.jpg', 1080, 1560),
        ]);

        $testimonial = Testimonial::first();
        $this->assertSame(1080, $testimonial->width);
        $this->assertSame(1560, $testimonial->height);
        $this->assertFalse($testimonial->isLandscape());

        $this->actingAs($admin)->put(route('admin.testimonials.update', $testimonial), [
            'image' => UploadedFile::fake()->image('landscape.jpg', 1200, 675),
        ])->assertRedirect(route('admin.testimonials.index'));

        $testimonial->refresh();

        $this->assertSame(1200, $testimonial->width);
        $this->assertSame(675, $testimonial->height);
        $this->assertTrue($testimonial->isLandscape());
    }

    public function test_orientation_is_reported_for_each_shape()
    {
        $landscape = new Testimonial(['width' => 1200, 'height' => 675]);
        $this->assertTrue($landscape->isLandscape());
        $this->assertSame('landscape', $landscape->orientation);
        $this->assertSame('1200 x 675 px', $landscape->dimensions);

        $portrait = new Testimonial(['width' => 1080, 'height' => 1560]);
        $this->assertFalse($portrait->isLandscape());
        $this->assertSame('portrait', $portrait->orientation);

        $square = new Testimonial(['width' => 800, 'height' => 800]);
        $this->assertFalse($square->isLandscape());
        $this->assertSame('square', $square->orientation);
    }

    public function test_size_and_dimension_labels_handle_missing_metadata()
    {
        $unknown = new Testimonial();

        $this->assertSame('—', $unknown->dimensions);
        $this->assertSame('—', $unknown->size_label);
        $this->assertSame('unknown', $unknown->orientation);
        $this->assertFalse($unknown->isLandscape());

        $this->assertSame('176 KB', (new Testimonial(['file_size' => 180224]))->size_label);
        $this->assertSame('1.5 MB', (new Testimonial(['file_size' => 1572864]))->size_label);
    }

    public function test_recommended_size_is_landscape()
    {
        $this->assertGreaterThan(
            Testimonial::RECOMMENDED_HEIGHT,
            Testimonial::RECOMMENDED_WIDTH,
            'The recommended size must be wider than it is tall.'
        );
    }

    public function test_admin_index_shows_the_size_of_every_image()
    {
        Storage::fake('public');
        $admin = $this->superAdmin();

        $this->actingAs($admin)->post(route('admin.testimonials.store'), [
            'image' => UploadedFile::fake()->image('landscape.jpg', 1200, 675),
        ]);

        $this->actingAs($admin)->get(route('admin.testimonials.index'))
            ->assertOk()
            ->assertSee('1200 x 675 px')
            ->assertSee('landscape');
    }

    public function test_admin_warns_when_an_image_is_not_landscape()
    {
        Storage::fake('public');
        $admin = $this->superAdmin();

        $this->actingAs($admin)->post(route('admin.testimonials.store'), [
            'image' => UploadedFile::fake()->image('portrait.jpg', 1080, 1560),
        ]);

        $this->actingAs($admin)->get(route('admin.testimonials.index'))
            ->assertOk()
            ->assertSee('1080 x 1560 px')
            ->assertSee('portrait')
            ->assertSee('not landscape');
    }

    public function test_admin_upload_screens_state_the_recommended_size()
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->get(route('admin.testimonials.create'))
            ->assertOk()
            ->assertSee(Testimonial::RECOMMENDED_WIDTH . ' x ' . Testimonial::RECOMMENDED_HEIGHT . ' px');
    }

    public function test_admin_edit_screen_shows_the_current_size()
    {
        Storage::fake('public');
        $admin = $this->superAdmin();

        $this->actingAs($admin)->post(route('admin.testimonials.store'), [
            'image' => UploadedFile::fake()->image('portrait.jpg', 1080, 1560),
        ]);

        $this->actingAs($admin)->get(route('admin.testimonials.edit', Testimonial::first()))
            ->assertOk()
            ->assertSee('1080 x 1560 px')
            ->assertSee('portrait');
    }

    public function test_home_page_slider_is_single_column_on_mobile_and_double_on_desktop()
    {
        $this->assertStringContainsString(
            'w-[85vw]',
            $this->slideClasses(),
            'Mobile should show one slide with a peek of the next.'
        );

        $this->assertStringContainsString(
            'lg:w-[calc(50%_-_0.75rem)]',
            $this->slideClasses(),
            'Desktop should fit exactly two slides per row.'
        );

        $this->assertStringNotContainsString(
            'max-w-sm',
            $this->slideClasses(),
            'A max-width cap would break the two-across layout.'
        );
    }

    public function test_home_page_slider_uses_a_landscape_frame()
    {
        $this->assertMatchesRegularExpression(
            '/<figure class="slide-item[^"]*".*?aspect-video.*?object-contain/s',
            $this->testimonialSliderMarkup(),
            'Slides should sit in a 16:9 landscape frame that does not crop the image.'
        );
    }

    public function test_home_page_never_crops_testimonial_images()
    {
        $this->assertStringNotContainsString(
            'object-cover',
            $this->testimonialSliderMarkup(),
            'object-cover would crop testimonials that do not match the frame.'
        );
    }

    private function slideClasses(): string
    {
        preg_match('/<figure class="slide-item[^"]*"/', $this->testimonialSliderMarkup(), $matches);

        return $matches[0] ?? '';
    }

    /**
     * The "Customer Testimonials" section of the home page, so these assertions
     * are not affected by product images elsewhere on the page.
     */
    private function testimonialSliderMarkup(): string
    {
        $view = file_get_contents(resource_path('views/store/index.blade.php'));

        preg_match('/<!-- Customer Testimonials -->(.*?)<!-- Info \/ Help Cards -->/s', $view, $matches);

        return $matches[1] ?? '';
    }
}
