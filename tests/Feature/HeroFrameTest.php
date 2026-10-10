<?php

namespace Tests\Feature;

use App\Models\Slider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HeroFrameTest extends TestCase
{
    use RefreshDatabase;

    private function render(array $sliders): string
    {
        foreach ($sliders as $i => $slider) {
            Slider::create($slider + ['is_active' => true, 'sort_order' => $i]);
        }

        return $this->get('/')->getContent();
    }

    public function test_the_hero_frame_is_a_fixed_wide_band()
    {
        $html = $this->render([
            ['image' => 'sliders/wide.jpg'],
            ['image' => 'sliders/tall.jpg'],
        ]);

        // A fixed frame is what makes every slide fill the same full-width band.
        $this->assertStringContainsString('md:[aspect-ratio:2.4]', $html);
        $this->assertStringNotContainsString('--hero-ratio', $html);
    }

    public function test_slide_video_covers_the_frame()
    {
        $html = $this->render([
            ['image' => 'sliders/vid.jpg', 'video' => 'sliders/videos/clip.mp4', 'video_type' => 'upload'],
        ]);

        $this->assertStringContainsString('class="hero-media w-full h-full object-cover object-center bg-black"', $html);
    }

    public function test_slide_image_covers_the_frame()
    {
        $html = $this->render([
            ['image' => 'sliders/photo.jpg'],
        ]);

        $this->assertStringContainsString('class="w-full h-full object-cover object-center" loading="eager"', $html);
    }

    public function test_a_portrait_video_is_no_longer_clamped_into_a_short_frame()
    {
        $html = $this->render([
            ['image' => 'sliders/vid.jpg', 'video' => 'sliders/videos/portrait.mp4', 'video_type' => 'upload'],
        ]);

        // The old code shrank the frame to the video's own ratio, clamped to a
        // minimum of 1.25, which cropped a 9:16 video down to a sliver.
        $this->assertStringNotContainsString('Math.max(1.25', $html);
        $this->assertStringNotContainsString('videoLoaded', $html);
        $this->assertStringNotContainsString('imgLoaded', $html);
    }

    public function test_the_youtube_cover_math_matches_the_fixed_desktop_frame()
    {
        $html = $this->render([
            ['video' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'video_type' => 'youtube'],
        ]);

        // An iframe has no object-fit, so it is oversized instead. For a fixed
        // 2.4 frame: width 1.6/2.4, height 2.4*0.9, both centred.
        $this->assertStringContainsString('.yt-cover-iframe { width: 74.07%; height: 135%; }', $html);
        $this->assertStringContainsString('yt-cover-iframe hero-media', $html);
    }
}