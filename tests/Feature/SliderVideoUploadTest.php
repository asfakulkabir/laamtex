<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\SliderController;
use App\Models\Slider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SliderVideoUploadTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        return User::factory()->create(['role' => User::ROLE_SUPER_ADMIN]);
    }

    public function test_create_form_posts_over_xhr_so_upload_progress_is_reported()
    {
        $response = $this->actingAs($this->superAdmin())->get(route('admin.sliders.create'));

        $response->assertOk()
            // A normal form submit cannot report byte progress, so the form is
            // intercepted and sent over XHR.
            ->assertSee('@submit.prevent="submitForm($event)"', false)
            ->assertSee('x-data="sliderUpload(', false)
            ->assertSee('function sliderUpload(limits)', false)
            ->assertSee("xhr.upload.addEventListener('progress'", false);
    }

    public function test_the_progress_bar_and_a_loading_state_are_rendered()
    {
        $this->actingAs($this->superAdmin())->get(route('admin.sliders.create'))
            ->assertOk()
            ->assertSee('x-show="uploading"', false)
            ->assertSee('x-text="statusText"', false)
            ->assertSee('x-text="progress + \'%\'"', false)
            // The submit button is disabled and relabelled while uploading.
            ->assertSee('x-bind:disabled="uploading"', false)
            ->assertSee('Uploading…', false);
    }

    public function test_a_finished_upload_is_not_reported_as_100_percent_until_the_server_is_done()
    {
        $this->actingAs($this->superAdmin())->get(route('admin.sliders.create'))
            ->assertOk()
            ->assertSee('Processing video…', false)
            // 100% is held back so a paused bar is never mistaken for a
            // finished upload while the server is still storing the file.
            ->assertSee('Math.min(99,', false);
    }

    public function test_the_form_advertises_the_same_limits_it_enforces()
    {
        $response = $this->actingAs($this->superAdmin())->get(route('admin.sliders.create'));

        $response->assertOk()
            ->assertSee(SliderController::VIDEO_ACCEPT, false)
            ->assertSee(SliderController::AUDIO_ACCEPT, false)
            ->assertSee((string) (SliderController::MAX_VIDEO_KB * 1024), false)
            ->assertSee((string) (SliderController::MAX_AUDIO_KB * 1024), false);
    }

    public function test_the_edit_form_reports_progress_too()
    {
        $slider = Slider::create(['image' => 'sliders/one.jpg', 'is_active' => true]);

        $this->actingAs($this->superAdmin())->get(route('admin.sliders.edit', $slider))
            ->assertOk()
            ->assertSee('@submit.prevent="submitForm($event)"', false)
            ->assertSee('x-text="statusText"', false);
    }

    public function test_a_video_over_the_limit_is_rejected_by_the_controller()
    {
        Storage::fake('public');

        // One kilobyte past the ceiling.
        $tooBig = SliderController::MAX_VIDEO_KB + 1;

        $this->actingAs($this->superAdmin())
            ->from(route('admin.sliders.create'))
            ->post(route('admin.sliders.store'), [
                'media_type' => 'video_upload',
                'video' => UploadedFile::fake()->create('clip.mp4', $tooBig, 'video/mp4'),
            ])
            ->assertRedirect(route('admin.sliders.create'))
            ->assertSessionHasErrors('video');

        $this->assertSame(0, Slider::count());
    }

    public function test_a_video_within_the_limit_is_stored()
    {
        Storage::fake('public');

        $this->actingAs($this->superAdmin())->post(route('admin.sliders.store'), [
            'media_type' => 'video_upload',
            'video' => UploadedFile::fake()->create('clip.mp4', 1024, 'video/mp4'),
            'title' => 'Launch',
        ])->assertRedirect(route('admin.sliders.index'));

        $slider = Slider::first();
        $this->assertTrue($slider->isUploadedVideo());
        Storage::disk('public')->assertExists($slider->video);
    }

    public function test_the_server_upload_ceiling_is_above_the_app_ceiling()
    {
        // A php.ini that rejects the file first would leave the admin with an
        // empty form instead of the validation message.
        foreach (['.user.ini', '.htaccess'] as $file) {
            $this->assertFileExists(base_path($file));
            $this->assertStringContainsString('64M', file_get_contents(base_path($file)));
        }

        $this->assertLessThan(64 * 1024, SliderController::MAX_VIDEO_KB);
    }
}