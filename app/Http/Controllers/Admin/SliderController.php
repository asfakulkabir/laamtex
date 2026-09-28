<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SliderController extends Controller
{
    protected const AUDIO_RULES = 'nullable|file|mimes:mp3,wav,ogg,m4a,aac,flac|mimetypes:audio/mpeg,audio/mp3,audio/wav,audio/x-wav,audio/wave,audio/ogg,audio/x-ogg,audio/vorbis,audio/mp4,audio/m4a,audio/aac,audio/x-aac,audio/flac,audio/x-flac|max:20480';

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
        $request->validate([
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'video' => 'nullable|file|mimes:mp4,webm,mov,ogv,m4v|max:51200',
            'audio' => self::AUDIO_RULES,
            'youtube_url' => 'nullable|url|max:1000',
            'media_type' => 'required|in:image,video_upload,youtube,audio',
            'title' => 'nullable|string|max:255',
            'link' => 'nullable|url|max:1000',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $data = $request->only(['title', 'link', 'sort_order', 'is_active']);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['sort_order'] = $request->input('sort_order', 0);

        $mediaType = $request->input('media_type', 'image');

        $data['image'] = null;
        $data['video'] = null;
        $data['video_type'] = null;
        $data['audio'] = null;

        if ($mediaType === 'image') {
            if (!$request->hasFile('image')) {
                return back()->withErrors(['image' => 'An image is required when media type is Image.'])->withInput();
            }
            $data['image'] = $request->file('image')->store('sliders', 'public');
        } elseif ($mediaType === 'audio') {
            if (!$request->hasFile('audio')) {
                return back()->withErrors(['audio' => 'An audio file is required when media type is Audio.'])->withInput();
            }
            $data['audio'] = $request->file('audio')->store('sliders/audio', 'public');
            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('sliders', 'public');
            }
        } elseif ($mediaType === 'video_upload' && $request->hasFile('video')) {
            $data['video'] = $request->file('video')->store('sliders/videos', 'public');
            $data['video_type'] = 'upload';
            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('sliders', 'public');
            }
        } elseif ($mediaType === 'video_upload') {
            return back()->withErrors(['video' => 'A video file is required when media type is Video Upload.'])->withInput();
        } elseif ($mediaType === 'youtube' && $request->filled('youtube_url')) {
            $data['video'] = trim($request->input('youtube_url'));
            $data['video_type'] = 'youtube';
            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('sliders', 'public');
            }
        } elseif ($mediaType === 'youtube') {
            return back()->withErrors(['youtube_url' => 'A YouTube video URL is required when media type is YouTube.'])->withInput();
        }

        Slider::create($data);

        return redirect()->route('admin.sliders.index')->with('success', 'Slider item added successfully.');
    }

    public function edit(Slider $slider)
    {
        return view('admin.sliders.edit', compact('slider'));
    }

    public function update(Request $request, Slider $slider)
    {
        $request->validate([
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'video' => 'nullable|file|mimes:mp4,webm,mov,ogv,m4v|max:51200',
            'audio' => self::AUDIO_RULES,
            'youtube_url' => 'nullable|url|max:1000',
            'media_type' => 'required|in:image,video_upload,youtube,audio',
            'title' => 'nullable|string|max:255',
            'link' => 'nullable|url|max:1000',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $data = $request->only(['title', 'link', 'sort_order', 'is_active']);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['sort_order'] = $request->input('sort_order', 0);

        $mediaType = $request->input('media_type', 'image');

        if ($mediaType === 'image') {
            $data['video'] = null;
            $data['video_type'] = null;
            $data['audio'] = null;
            if ($request->hasFile('image')) {
                $this->deleteImage($slider);
                $data['image'] = $request->file('image')->store('sliders', 'public');
            }
        } elseif ($mediaType === 'audio') {
            if ($request->hasFile('audio')) {
                $this->deleteAudio($slider);
                $data['audio'] = $request->file('audio')->store('sliders/audio', 'public');
            } elseif (!$slider->isAudio()) {
                return back()->withErrors(['audio' => 'An audio file is required when media type is Audio.'])->withInput();
            }
            $data['video'] = null;
            $data['video_type'] = null;
            if ($request->hasFile('image')) {
                $this->deleteImage($slider);
                $data['image'] = $request->file('image')->store('sliders', 'public');
            }
        } elseif ($mediaType === 'video_upload') {
            $data['audio'] = null;
            if ($request->hasFile('video')) {
                $this->deleteVideo($slider);
                $data['video'] = $request->file('video')->store('sliders/videos', 'public');
                $data['video_type'] = 'upload';
            } elseif (!$slider->isUploadedVideo()) {
                return back()->withErrors(['video' => 'A video file is required when media type is Video Upload.'])->withInput();
            }
            if ($request->hasFile('image')) {
                $this->deleteImage($slider);
                $data['image'] = $request->file('image')->store('sliders', 'public');
            }
        } elseif ($mediaType === 'youtube') {
            $data['audio'] = null;
            if ($request->filled('youtube_url')) {
                $data['video'] = trim($request->input('youtube_url'));
                $data['video_type'] = 'youtube';
            } elseif (!$slider->isYoutubeVideo()) {
                return back()->withErrors(['youtube_url' => 'A YouTube video URL is required when media type is YouTube.'])->withInput();
            }
            if ($request->hasFile('image')) {
                $this->deleteImage($slider);
                $data['image'] = $request->file('image')->store('sliders', 'public');
            }
        }

        $slider->update($data);

        return redirect()->route('admin.sliders.index')->with('success', 'Slider item updated successfully.');
    }

    public function destroy(Slider $slider)
    {
        $this->deleteImage($slider);
        $this->deleteVideo($slider);
        $this->deleteAudio($slider);
        $slider->delete();

        return redirect()->route('admin.sliders.index')->with('success', 'Slider item deleted successfully.');
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
