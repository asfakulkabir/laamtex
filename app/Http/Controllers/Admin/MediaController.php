<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    /**
     * Kept in step with SliderController so the same file is accepted in both
     * places, and with .user.ini so the server can actually receive it.
     */
    public const MAX_IMAGE_KB = 10240;

    public const MAX_VIDEO_KB = 51200;

    public const MAX_AUDIO_KB = 20480;

    public function index(Request $request)
    {
        $media = Media::query()
            ->ofKind($request->query('kind'))
            ->search($request->query('q'))
            ->latest()
            ->paginate(48)
            ->withQueryString();

        return view('admin.media.index', [
            'media' => $media,
            'kind' => $request->query('kind'),
            'query' => $request->query('q'),
        ]);
    }

    /**
     * JSON list for the picker modal. Kept small so a large library does not
     * stall the browser every time a field is opened.
     */
    public function list(Request $request)
    {
        $items = Media::query()
            ->ofKind($request->query('kind'))
            ->search($request->query('q'))
            ->latest()
            ->limit((int) min(120, max(1, $request->query('limit', 60))))
            ->get()
            ->map(fn (Media $item) => [
                'id' => $item->id,
                'name' => $item->name,
                'kind' => $item->kind,
                'url' => $item->url,
                'size' => $item->sizeForHumans(),
                'dimensions' => $item->width && $item->height ? $item->width.'×'.$item->height : null,
            ]);

        return response()->json(['items' => $items]);
    }

    public function store(Request $request)
    {
        $file = $request->file('file');

        // PHP silently drops a file that is too big for upload_max_filesize or
        // post_max_size. Laravel would then just say "The file failed to
        // upload," which leaves the admin guessing, so say which ceiling bit.
        if ($file instanceof \Illuminate\Http\UploadedFile && ! $file->isValid()) {
            return $this->respond($request, [
                'ok' => false,
                'message' => 'The server rejected the file before it finished uploading.'
                    .' This host accepts at most '.ini_get('upload_max_filesize').' per file and '
                    .ini_get('post_max_size').' for the whole request.'
                    .' Try a smaller video, or ask the host to raise post_max_size and upload_max_filesize.',
            ], 422);
        }

        $request->validate([
            'file' => [
                'required',
                'file',
                'mimes:jpeg,jpg,png,webp,gif,mp4,webm,mov,ogv,m4v,mkv,avi,mpeg,mpg,3gp,ts,flv,wmv,mp3,wav,ogg,m4a,aac,flac',
                'max:'.self::MAX_VIDEO_KB,
            ],
        ], [
            'file.max' => 'That file is larger than '.round(self::MAX_VIDEO_KB / 1024).' MB.',
            'file.mimes' => 'Only images, videos and audio files are allowed.',
        ]);

        $hash = hash_file('sha256', $file->getRealPath());

        // The same file uploaded twice is the same asset, so reuse the row
        // instead of storing a second copy.
        $existing = $hash ? Media::where('hash', $hash)->first() : null;

        if ($existing && Storage::disk($existing->disk ?: 'public')->exists($existing->path)) {
            return $this->respond($request, [
                'ok' => true,
                'duplicate' => true,
                'media' => $this->payload($existing),
                'message' => 'That file is already in the library.',
            ]);
        }

        $media = Media::storeUpload($file, 'media');

        return $this->respond($request, [
            'ok' => true,
            'media' => $this->payload($media),
            'message' => 'Uploaded.',
        ]);
    }

    public function destroy(Media $media)
    {
        $usage = $media->usage();

        if ($usage !== []) {
            $places = implode(', ', array_keys($usage));

            return $this->respond(request(), [
                'ok' => false,
                'message' => 'This file is still used by: '.$places.'. Remove it there first.',
            ], 409);
        }

        Storage::disk($media->disk ?: 'public')->delete($media->path);
        $media->delete();

        return $this->respond(request(), [
            'ok' => true,
            'message' => 'File deleted.',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function payload(Media $media): array
    {
        return [
            'id' => $media->id,
            'name' => $media->name,
            'kind' => $media->kind,
            'url' => $media->url,
            'size' => $media->sizeForHumans(),
            'dimensions' => $media->width && $media->height ? $media->width.'×'.$media->height : null,
        ];
    }

    /**
     * A normal redirect for a plain form post, JSON for the uploader.
     */
    protected function respond(Request $request, array $data, int $status = 200)
    {
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json($data, $status);
        }

        if (($data['ok'] ?? false) === false) {
            return back()->with('error', $data['message']);
        }

        return back()->with('success', $data['message']);
    }

    /**
     * Limits handed to the browser so it can reject a file before it starts.
     */
    public static function limits(): array
    {
        return [
            'maxBytes' => self::MAX_VIDEO_KB * 1024,
            'accept' => 'image/jpeg,image/png,image/webp,image/gif,'
                .'video/mp4,video/webm,video/quicktime,video/ogg,video/x-m4v,'
                .'video/x-matroska,video/x-msvideo,video/mpeg,video/3gpp,video/mp2t,video/x-flv,video/x-ms-wmv,'
                .'audio/mpeg,audio/wav,audio/ogg,audio/mp4,audio/aac,audio/flac',
        ];
    }
}