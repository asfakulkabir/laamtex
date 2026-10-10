<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\Slider;
use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SliderController extends Controller
{
    /**
     * Upload ceilings, in kilobytes. The views and the browser-side check read
     * these too, so the size shown to the admin is the size that is enforced.
     *
     * These must stay under the server's `upload_max_filesize` and
     * `post_max_size`. The project ships a .user.ini and .htaccess override
     * that raise both to 64M so a 50 MB video is never cut off by a default
     * 2M/8M php.ini before validation ever runs.
     */
    public const MAX_IMAGE_KB = 5120;

    public const MAX_VIDEO_KB = 51200;

    public const MAX_AUDIO_KB = 20480;

    public const IMAGE_ACCEPT = 'image/jpeg,image/png,image/webp';

    public const VIDEO_ACCEPT = 'video/mp4,video/webm,video/quicktime,video/ogg,video/x-m4v,video/x-matroska,video/x-msvideo,video/mpeg,video/3gpp,video/mp2t,video/x-flv,video/x-ms-wmv';

    public const AUDIO_ACCEPT = 'audio/mpeg,audio/wav,audio/ogg,audio/mp4,audio/aac,audio/x-m4a,audio/flac';

    protected const AUDIO_RULES = 'nullable|file|mimes:mp3,wav,ogg,m4a,aac,flac|mimetypes:audio/mpeg,audio/mp3,audio/wav,audio/x-wav,audio/wave,audio/ogg,audio/x-ogg,audio/vorbis,audio/mp4,audio/m4a,audio/aac,audio/x-aac,audio/flac,audio/x-flac|max:'.self::MAX_AUDIO_KB;

    /**
     * The same limits in the shape the browser side wants, so a file that is
     * too large is rejected before a slow upload starts.
     */
    public static function uploadLimits(): array
    {
        return [
            'image' => [
                'label' => 'Image',
                'maxBytes' => self::MAX_IMAGE_KB * 1024,
                'accept' => self::IMAGE_ACCEPT,
            ],
            'video' => [
                'label' => 'Video',
                'maxBytes' => self::MAX_VIDEO_KB * 1024,
                'accept' => self::VIDEO_ACCEPT,
            ],
            'audio' => [
                'label' => 'Audio',
                'maxBytes' => self::MAX_AUDIO_KB * 1024,
                'accept' => self::AUDIO_ACCEPT,
            ],
        ];
    }

    /**
     * Validation shared by store() and update().
     */
    protected function rules(): array
    {
        return [
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:'.self::MAX_IMAGE_KB,
            'video' => 'nullable|file|mimes:mp4,webm,mov,ogv,m4v,mkv,avi,mpeg,mpg,3gp,ts,flv,wmv|max:'.self::MAX_VIDEO_KB,
            'audio' => self::AUDIO_RULES,
            'youtube_url' => 'nullable|url|max:1000',
            'media_type' => 'required|in:image,video_upload,youtube,audio',
            'title' => 'nullable|string|max:255',
            'link' => 'nullable|url|max:1000',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ];
    }

    public function index()
    {
        $sliders = Slider::orderBy('sort_order')->get();
        return view('admin.sliders.index', compact('sliders'));
    }

    public function create()
    {
        return view('admin.sliders.create');
    }

    public function store(Request $request)
    {
        $request->validate($this->rules());

        $media = app(MediaService::class);

        $data = $request->only(['title', 'link', 'sort_order', 'is_active']);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['sort_order'] = $request->input('sort_order', 0);

        $mediaType = $request->input('media_type', 'image');

        $data['image'] = null;
        $data['video'] = null;
        $data['video_type'] = null;
        $data['audio'] = null;

        if ($mediaType === 'image') {
            if (! $media->hasAny($request, 'image', Media::KIND_IMAGE)) {
                return $this->fail($request, 'image', 'An image is required when media type is Image.');
            }
            $data['image'] = $media->resolve($request, 'image', 'sliders', Media::KIND_IMAGE);
        } elseif ($mediaType === 'audio') {
            if (! $media->hasAny($request, 'audio', Media::KIND_AUDIO)) {
                return $this->fail($request, 'audio', 'An audio file is required when media type is Audio.');
            }
            $data['audio'] = $media->resolve($request, 'audio', 'sliders/audio', Media::KIND_AUDIO);
            $data['image'] = $media->resolve($request, 'image', 'sliders', Media::KIND_IMAGE);
        } elseif ($mediaType === 'video_upload') {
            if (! $media->hasAny($request, 'video', Media::KIND_VIDEO)) {
                return $this->fail($request, 'video', 'A video file is required when media type is Video Upload.');
            }
            $data['video'] = $media->resolve($request, 'video', 'sliders/videos', Media::KIND_VIDEO);
            $data['video_type'] = 'upload';
            $data['image'] = $media->resolve($request, 'image', 'sliders', Media::KIND_IMAGE);
        } elseif ($mediaType === 'youtube') {
            if (! $request->filled('youtube_url')) {
                return $this->fail($request, 'youtube_url', 'A YouTube video URL is required when media type is YouTube.');
            }
            $data['video'] = trim($request->input('youtube_url'));
            $data['video_type'] = 'youtube';
            $data['image'] = $media->resolve($request, 'image', 'sliders', Media::KIND_IMAGE);
        }

        Slider::create($data);

        return $this->saved($request, 'Slider item added successfully.');
    }

    public function edit(Slider $slider)
    {
        return view('admin.sliders.edit', compact('slider'));
    }

    public function update(Request $request, Slider $slider)
    {
        $request->validate($this->rules());

        $data = $request->only(['title', 'link', 'sort_order', 'is_active']);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['sort_order'] = $request->input('sort_order', 0);

        $mediaType = $request->input('media_type', 'image');

        $media = app(MediaService::class);

        if ($mediaType === 'image') {
            $data['video'] = null;
            $data['video_type'] = null;
            $data['audio'] = null;
            if ($media->hasAny($request, 'image', Media::KIND_IMAGE)) {
                $this->deleteImage($slider);
                $data['image'] = $media->resolve($request, 'image', 'sliders', Media::KIND_IMAGE);
            }
        } elseif ($mediaType === 'audio') {
            if ($media->hasAny($request, 'audio', Media::KIND_AUDIO)) {
                $this->deleteAudio($slider);
                $data['audio'] = $media->resolve($request, 'audio', 'sliders/audio', Media::KIND_AUDIO);
            } elseif (! $slider->isAudio()) {
                return $this->fail($request, 'audio', 'An audio file is required when media type is Audio.');
            }
            $data['video'] = null;
            $data['video_type'] = null;
            if ($media->hasAny($request, 'image', Media::KIND_IMAGE)) {
                $this->deleteImage($slider);
                $data['image'] = $media->resolve($request, 'image', 'sliders', Media::KIND_IMAGE);
            }
        } elseif ($mediaType === 'video_upload') {
            $data['audio'] = null;
            if ($media->hasAny($request, 'video', Media::KIND_VIDEO)) {
                $this->deleteVideo($slider);
                $data['video'] = $media->resolve($request, 'video', 'sliders/videos', Media::KIND_VIDEO);
                $data['video_type'] = 'upload';
            } elseif (! $slider->isUploadedVideo()) {
                return $this->fail($request, 'video', 'A video file is required when media type is Video Upload.');
            }
            if ($media->hasAny($request, 'image', Media::KIND_IMAGE)) {
                $this->deleteImage($slider);
                $data['image'] = $media->resolve($request, 'image', 'sliders', Media::KIND_IMAGE);
            }
        } elseif ($mediaType === 'youtube') {
            $data['audio'] = null;
            if ($request->filled('youtube_url')) {
                $data['video'] = trim($request->input('youtube_url'));
                $data['video_type'] = 'youtube';
            } elseif (! $slider->isYoutubeVideo()) {
                return $this->fail($request, 'youtube_url', 'A YouTube video URL is required when media type is YouTube.');
            }
            if ($media->hasAny($request, 'image', Media::KIND_IMAGE)) {
                $this->deleteImage($slider);
                $data['image'] = $media->resolve($request, 'image', 'sliders', Media::KIND_IMAGE);
            }
        }

        $slider->update($data);

        return $this->saved($request, 'Slider item updated successfully.');
    }

    public function destroy(Slider $slider)
    {
        $this->deleteImage($slider);
        $this->deleteVideo($slider);
        $this->deleteAudio($slider);
        $slider->delete();

        return redirect()->route('admin.sliders.index')->with('success', 'Slider item deleted successfully.');
    }

    /**
     * The uploader posts over XHR, where a redirect is useless because the
     * browser cannot tell success from a validation bounce. An XHR caller gets
     * JSON with the message and the page to follow; a plain form post still gets
     * the ordinary redirect.
     */
    protected function saved(Request $request, string $message)
    {
        if ($this->wantsJson($request)) {
            return response()->json([
                'ok' => true,
                'redirect' => route('admin.sliders.index'),
                'message' => $message,
            ]);
        }

        return redirect()->route('admin.sliders.index')->with('success', $message);
    }

    protected function fail(Request $request, string $field, string $message)
    {
        if ($this->wantsJson($request)) {
            return response()->json([
                'ok' => false,
                'errors' => [$field => [$message]],
                'message' => $message,
            ], 422);
        }

        return back()->withErrors([$field => $message])->withInput();
    }

    protected function wantsJson(Request $request): bool
    {
        return $request->expectsJson() || $request->wantsJson();
    }

    protected function deleteImage(Slider $slider)
    {
        if ($slider->image) {
            Storage::disk('public')->delete($slider->image);
        }
    }

    protected function deleteVideo(Slider $slider)
    {
        if ($slider->isUploadedVideo() && $slider->video) {
            Storage::disk('public')->delete($slider->video);
        }
    }

    protected function deleteAudio(Slider $slider)
    {
        if ($slider->isAudio() && $slider->audio) {
            Storage::disk('public')->delete($slider->audio);
        }
    }
}
