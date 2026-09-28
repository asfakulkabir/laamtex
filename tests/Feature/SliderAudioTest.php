<?php

namespace Tests\Feature;

use App\Models\Slider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SliderAudioTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        return User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
    }

    public function test_super_admin_can_upload_an_audio_slider()
    {
        Storage::fake('public');
        $admin = $this->superAdmin();

        $this->actingAs($admin)->get(route('admin.sliders.create'))
            ->assertOk()
            ->assertSee('Audio', false);

        $this->actingAs($admin)->post(route('admin.sliders.store'), [
            'media_type' => 'audio',
            'audio' => UploadedFile::fake()->create('track.mp3', 120, 'audio/mpeg'),
            'title' => 'Wedding Collection',
        ])->assertRedirect(route('admin.sliders.index'));

        $slider = Slider::first();
        $this->assertTrue($slider->isAudio());
        $this->assertNull($slider->video);
        $this->assertSame('Wedding Collection', $slider->title);
        Storage::disk('public')->assertExists($slider->audio);
    }

    public function test_audio_file_is_required_for_audio_media_type()
    {
        Storage::fake('public');
        $admin = $this->superAdmin();

        $this->actingAs($admin)->from(route('admin.sliders.create'))
            ->post(route('admin.sliders.store'), ['media_type' => 'audio'])
            ->assertRedirect(route('admin.sliders.create'))
            ->assertSessionHasErrors('audio');

        $this->assertSame(0, Slider::count());
    }

    public function test_switching_media_type_to_audio_replaces_the_video()
    {
        Storage::fake('public');
        $admin = $this->superAdmin();

        $this->actingAs($admin)->post(route('admin.sliders.store'), [
            'media_type' => 'video_upload',
            'video' => UploadedFile::fake()->create('clip.mp4', 200, 'video/mp4'),
        ])->assertRedirect(route('admin.sliders.index'));

        $slider = Slider::first();
        Storage::disk('public')->assertExists($slider->video);

        $this->actingAs($admin)->put(route('admin.sliders.update', $slider->id), [
            'media_type' => 'audio',
            'audio' => UploadedFile::fake()->create('track.mp3', 120, 'audio/mpeg'),
        ])->assertRedirect(route('admin.sliders.index'));

        $slider->refresh();
        $this->assertTrue($slider->isAudio());
        $this->assertFalse($slider->isVideo());
        Storage::disk('public')->assertMissing($slider->getRawOriginal('video'));
    }

    public function test_home_page_renders_the_audio_player()
    {
        Storage::fake('public');
        $admin = $this->superAdmin();

        $this->actingAs($admin)->post(route('admin.sliders.store'), [
            'media_type' => 'audio',
            'audio' => UploadedFile::fake()->create('track.mp3', 120, 'audio/mpeg'),
            'image' => UploadedFile::fake()->image('cover.jpg', 1920, 800),
        ])->assertRedirect(route('admin.sliders.index'));

        $slider = Slider::first();
        $audioUrl = asset('storage/' . $slider->audio);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee($audioUrl, false)
            ->assertSee('toggleAudio(0)', false);
    }

    public function test_deleting_a_slider_removes_the_audio_file()
    {
        Storage::fake('public');
        $admin = $this->superAdmin();

        $this->actingAs($admin)->post(route('admin.sliders.store'), [
            'media_type' => 'audio',
            'audio' => UploadedFile::fake()->create('track.mp3', 120, 'audio/mpeg'),
        ])->assertRedirect(route('admin.sliders.index'));

        $slider = Slider::first();
        $path = $slider->audio;

        $this->actingAs($admin)->delete(route('admin.sliders.destroy', $slider->id))
            ->assertRedirect(route('admin.sliders.index'));

        $this->assertNull(Slider::first());
        Storage::disk('public')->assertMissing($path);
    }
}
