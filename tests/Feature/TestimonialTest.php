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
}
