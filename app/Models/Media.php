<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    public const KIND_IMAGE = 'image';

    public const KIND_VIDEO = 'video';

    public const KIND_AUDIO = 'audio';

    /**
     * Columns across the app that store a path into the media library. Checked
     * before deleting so an asset in use cannot break a live slider or product.
     *
     * @var array<int, string>
     */
    protected const USAGE_COLUMNS = [
        'sliders.image',
        'sliders.video',
        'sliders.audio',
        'product_images.image',
        'product_variations.image',
        'testimonials.image',
        'size_charts.image',
    ];

    protected $fillable = [
        'name', 'path', 'disk', 'kind', 'mime', 'size', 'width', 'height', 'hash', 'created_by',
    ];

    protected $casts = [
        'size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'created_by' => 'integer',
    ];

    protected $appends = ['url'];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk ?: 'public')->url($this->path);
    }

    public function isImage(): bool
    {
        return $this->kind === self::KIND_IMAGE;
    }

    public function isVideo(): bool
    {
        return $this->kind === self::KIND_VIDEO;
    }

    public function isAudio(): bool
    {
        return $this->kind === self::KIND_AUDIO;
    }

    public function scopeOfKind(Builder $query, ?string $kind): Builder
    {
        return $kind ? $query->where('kind', $kind) : $query;
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        return $query->where('name', 'like', '%'.$term.'%');
    }

    public function sizeForHumans(): string
    {
        $bytes = (int) $this->size;

        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1).' MB';
        }

        if ($bytes >= 1024) {
            return max(1, round($bytes / 1024)).' KB';
        }

        return $bytes.' B';
    }

    /**
     * Where else this exact path is referenced. Used to stop a delete that
     * would leave a broken image on a live page.
     *
     * @return array<string, int>
     */
    public function usage(): array
    {
        $usage = [];

        foreach (self::USAGE_COLUMNS as $column) {
            [$table, $field] = explode('.', $column);

            $count = \Illuminate\Support\Facades\DB::table($table)
                ->where($field, $this->path)
                ->count();

            if ($count > 0) {
                $usage[$column] = $count;
            }
        }

        return $usage;
    }

    public function isUsed(): bool
    {
        return $this->usage() !== [];
    }

    /**
     * Is this path (or file name) one of the files recorded in the library?
     *
     * When an entity is replaced or deleted, the file behind a library pick is
     * left in place so the same asset can keep being reused elsewhere. Only
     * paths that were never part of the library are cleaned up.
     */
    public static function isLibraryPath(?string $path): bool
    {
        return $path !== null
            && $path !== ''
            && self::where('path', $path)->exists();
    }

    /**
     * Store an uploaded file into the library.
     */
    public static function storeUpload($file, string $directory = 'media'): self
    {
        $kind = self::kindFor($file->getMimeType());

        $path = $file->store($directory, 'public');

        [$width, $height] = self::dimensionsFor($path, $kind);

        return self::create([
            'name' => $file->getClientOriginalName(),
            'path' => $path,
            'disk' => 'public',
            'kind' => $kind,
            'mime' => $file->getMimeType(),
            'size' => $file->getSize() ?: 0,
            'width' => $width,
            'height' => $height,
            'hash' => hash_file('sha256', $file->getRealPath()) ?: null,
            'created_by' => auth()->id(),
        ]);
    }

    public static function kindFor(?string $mime): string
    {
        return match (true) {
            (bool) str_starts_with((string) $mime, 'image/') => self::KIND_IMAGE,
            (bool) str_starts_with((string) $mime, 'video/') => self::KIND_VIDEO,
            (bool) str_starts_with((string) $mime, 'audio/') => self::KIND_AUDIO,
            default => self::KIND_IMAGE,
        };
    }

    /**
     * @return array{0: int|null, 1: int|null}
     */
    protected static function dimensionsFor(string $path, string $kind): array
    {
        if ($kind !== self::KIND_IMAGE) {
            return [null, null];
        }

        $absolute = Storage::disk('public')->path($path);

        if (! is_file($absolute)) {
            return [null, null];
        }

        $size = @getimagesize($absolute);

        return $size ? [(int) $size[0], (int) $size[1]] : [null, null];
    }
}