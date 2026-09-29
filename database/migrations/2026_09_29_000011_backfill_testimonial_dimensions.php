<?php

use App\Models\Testimonial;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    public function up(): void
    {
        // Dimensions were not recorded before this column existed, so read them
        // back off the files that are already on disk.
        Testimonial::query()
            ->where(function ($query) {
                $query->whereNull('width')->orWhereNull('height');
            })
            ->each(function (Testimonial $testimonial) {
                if (! $testimonial->image) {
                    return;
                }

                $path = Storage::disk('public')->path($testimonial->image);

                if (! is_file($path)) {
                    return;
                }

                $size = @getimagesize($path);

                $testimonial->forceFill([
                    'width' => $size !== false ? (int) $size[0] : null,
                    'height' => $size !== false ? (int) $size[1] : null,
                    'file_size' => filesize($path) ?: null,
                ])->saveQuietly();
            });
    }

    public function down(): void
    {
        // Recorded metadata, nothing to roll back.
    }
};
