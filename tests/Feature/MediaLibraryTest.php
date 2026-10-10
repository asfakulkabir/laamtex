<?php
namespace Tests\Feature;

use App\Models\Media;
use App\Models\Slider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaLibraryTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        return User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
    }

    public function test_the_library_page_renders()
    {
        Storage::fake('public');

        $this->actingAs($this->superAdmin())->get(route('admin.media.index'))
            ->assertOk()
            ->assertSee('Media Library')
            ->assertSee('Media Library', false);
    }

    public function test_an_image_upload_lands_in_the_library()
    {
        Storage::fake('public');

        $this->actingAs($this->superAdmin())
            ->postJson(route('admin.media.store'), [
                'file' => UploadedFile::fake()->image('hero.png', 400, 300),
            ])
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('media.kind', 'image')
            ->assertJsonPath('media.dimensions', '400×300');

        $this->assertSame(1, Media::count());
        Storage::disk('public')->assertExists(Media::first()->path);
    }

    public function test_a_video_upload_lands_in_the_library()
    {
        Storage::fake('public');

        $this->actingAs($this->superAdmin())
            ->postJson(route('admin.media.store'), [
                'file' => UploadedFile::fake()->create('clip.mp4', 512, 'video/mp4'),
            ])
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('media.kind', 'video');

        $this->assertTrue(Media::first()->isVideo());
    }

    public function test_the_same_file_is_not_stored_twice()
    {
        Storage::fake('public');
        $admin = $this->superAdmin();

        $file = UploadedFile::fake()->image('same.png', 200, 200);

        $this->actingAs($admin)->postJson(route('admin.media.store'), ['file' => $file])->assertOk();

        $this->actingAs($admin)
            ->postJson(route('admin.media.store'), ['file' => UploadedFile::fake()->image('same.png', 200, 200)])
            ->assertOk()
            ->assertJsonPath('duplicate', true);

        $this->assertSame(1, Media::count());
    }

    public function test_the_picker_list_is_filterable()
    {
        Storage::fake('public');

        Media::storeUpload(UploadedFile::fake()->image('pic.png'), 'media');
        Media::storeUpload(UploadedFile::fake()->create('clip.mp4', 32, 'video/mp4'), 'media');

        $this->actingAs($this->superAdmin())
            ->getJson(route('admin.media.list', ['kind' => 'video']))
            ->assertOk()
            ->assertJsonCount(1, 'items')
            ->assertJsonPath('items.0.kind', 'video');
    }

    public function test_a_slider_can_be_created_from_the_library_without_a_new_upload()
    {
        Storage::fake('public');
        $admin = $this->superAdmin();

        $media = Media::storeUpload(UploadedFile::fake()->image('library-pick.png'), 'media');

        $this->actingAs($admin)->post(route('admin.sliders.store'), [
            'media_type' => 'image',
            'image_media_id' => $media->id,
            'title' => 'From library',
        ])->assertRedirect(route('admin.sliders.index'));

        $this->assertSame($media->path, Slider::first()->image);
    }

    public function test_a_file_in_use_cannot_be_deleted_from_the_library()
    {
        Storage::fake('public');

        $media = Media::storeUpload(UploadedFile::fake()->image('in-use.png'), 'media');
        Slider::create(['image' => $media->path, 'is_active' => true]);

        $this->actingAs($this->superAdmin())
            ->deleteJson(route('admin.media.destroy', $media))
            ->assertStatus(409)
            ->assertJsonPath('ok', false);

        $this->assertSame(1, Media::count());
        Storage::disk('public')->assertExists($media->path);
    }

    public function test_an_unused_file_is_deleted()
    {
        Storage::fake('public');

        $media = Media::storeUpload(UploadedFile::fake()->image('free.png'), 'media');

        $this->actingAs($this->superAdmin())
            ->deleteJson(route('admin.media.destroy', $media))
            ->assertOk()
            ->assertJsonPath('ok', true);

        $this->assertSame(0, Media::count());
        Storage::disk('public')->assertMissing($media->path);
    }

    public function test_a_missing_file_is_not_written_into_a_slider()
    {
        Storage::fake('public');

        // A pick pointing at a row whose file has vanished must not be stored.
        $media = Media::create([
            'name' => 'gone.png', 'path' => 'media/gone.png', 'kind' => 'image',
            'disk' => 'public', 'size' => 10, 'mime' => 'image/png',
        ]);

        $this->actingAs($this->superAdmin())
            ->from(route('admin.sliders.create'))
            ->post(route('admin.sliders.store'), [
                'media_type' => 'image',
                'image_media_id' => $media->id,
            ])
            ->assertSessionHasErrors('image');

        $this->assertSame(0, Slider::count());
    }

    public function test_the_picker_is_offered_on_the_slider_forms()
    {
        $slider = Slider::create(['image' => 'sliders/keep.jpg', 'is_active' => true]);

        $response = $this->actingAs($this->superAdmin())->get(route('admin.sliders.create'));

        $response->assertOk()
            ->assertSee('Choose from library', false)
            ->assertSee('Upload from PC', false)
            // The names are bound so one picker also works inside an Alpine loop.
            ->assertSee(':name="mediaIdInputName"', false)
            ->assertSee('"field":"image"', false)
            ->assertSee('"field":"video"', false)
            ->assertSee('"field":"audio"', false);

        $this->actingAs($this->superAdmin())->get(route('admin.sliders.edit', $slider))
            ->assertOk()
            ->assertSee('Choose from library', false);
    }

    public function test_the_variation_rows_offer_the_picker()
    {
        $product = \App\Models\Product::factory()->create();

        $this->actingAs($this->superAdmin())->get(route('admin.products.edit', $product))
            ->assertOk()
            ->assertSee('`variations[${index}][image]`', false)
            ->assertSee(':name="mediaIdInputName"', false);
    }

    public function test_the_picker_is_told_the_same_size_limit_the_server_enforces()
    {
        $this->actingAs($this->superAdmin())->get(route('admin.sliders.create'))
            ->assertOk()
            ->assertSee((string) (\App\Http\Controllers\Admin\MediaController::MAX_VIDEO_KB * 1024), false)
            ->assertSee((string) (\App\Http\Controllers\Admin\SliderController::MAX_VIDEO_KB * 1024), false);
    }

    public function test_an_xhr_slider_save_answers_with_json()
    {
        Storage::fake('public');

        $this->actingAs($this->superAdmin())
            ->postJson(route('admin.sliders.store'), ['media_type' => 'video_upload'])
            ->assertStatus(422)
            ->assertJsonPath('ok', false)
            ->assertJsonStructure(['ok', 'message', 'errors']);
    }
}
