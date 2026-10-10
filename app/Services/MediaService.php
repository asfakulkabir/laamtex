<?php

namespace App\Services;

use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Resolves a media field that may arrive either as a fresh upload or as an id
 * picked from the media library.
 *
 * Every admin screen keeps its normal file input; adding the picker just means
 * also sending "<field>_media_id". Whichever one arrives, the caller gets back
 * a path to store on the public disk, so the rest of the app is unchanged.
 */
class MediaService
{
    /**
     * @return string|null The stored path, or null when nothing was supplied.
     */
    public function resolve(
        Request $request,
        string $field,
        string $directory,
        ?string $expectedKind = null,
        bool $required = false
    ): ?string {
        // A fresh upload wins, so an existing picker choice never silently
        // overrides a file the admin just dropped in.
        if ($request->hasFile($field)) {
            $file = $request->file($field);

            // Record it in the library too, so the asset is reusable later.
            $media = Media::storeUpload($file, $directory);

            return $media->path;
        }

        $picked = $this->pickedPath($request, $field, $expectedKind);

        if ($picked) {
            return $picked;
        }

        return null;
    }

    /**
     * The library file chosen for a field, if any.
     */
    public function pickedPath(Request $request, string $field, ?string $expectedKind = null): ?string
    {
        $id = $request->input($field.'_media_id');

        if (! $id) {
            return null;
        }

        $media = Media::find($id);

        if (! $media) {
            return null;
        }

        if ($expectedKind && $media->kind !== $expectedKind) {
            return null;
        }

        // A pick that points at a file which has since been removed must not
        // be written into the database.
        if (! Storage::disk($media->disk ?: 'public')->exists($media->path)) {
            return null;
        }

        return $media->path;
    }

    /**
     * Has the admin supplied anything usable for this field, by either route?
     *
     * A pick is only counted when it still resolves to a file on disk, so a
     * stale id cannot create a record that points at nothing.
     */
    public function hasAny(Request $request, string $field, ?string $expectedKind = null): bool
    {
        if ($request->hasFile($field)) {
            return true;
        }

        return $this->pickedPath($request, $field, $expectedKind) !== null;
    }

    /**
     * Remove the file behind a path, ignoring one that is not on our disk.
     */
    public function delete(string $path, string $disk = 'public'): void
    {
        if ($path) {
            Storage::disk($disk)->delete($path);
        }
    }
}