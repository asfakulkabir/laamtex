<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class Testimonial extends Model
{
    use HasFactory;

    /**
     * The home page slider shows two cards side by side on large screens and
     * one on mobile, so landscape artwork fills the frame without letterboxing.
     */
    public const RECOMMENDED_WIDTH = 1200;

    public const RECOMMENDED_HEIGHT = 675;

    protected $fillable = [
        'image',
        'width',
        'height',
        'file_size',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'width' => 'integer',
        'height' => 'integer',
        'file_size' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? Storage::url($this->image) : null;
    }

    /**
     * Read an uploaded image's pixel dimensions and byte size.
     *
     * getimagesize() is part of core PHP, so this needs neither GD nor
     * Imagick, and it understands every format the upload rule accepts.
     *
     * @return array{width:int|null,height:int|null,file_size:int|null}
     */
    public static function readImageMeta(UploadedFile $file): array
    {
        $meta = ['width' => null, 'height' => null, 'file_size' => $file->getSize()];

        $size = @getimagesize($file->getRealPath());

        if ($size !== false) {
            $meta['width'] = (int) $size[0];
            $meta['height'] = (int) $size[1];
        }

        return $meta;
    }

    /**
     * "1200 x 675 px", or a dash when the dimensions were never recorded.
     */
    public function getDimensionsAttribute(): string
    {
        if (! $this->width || ! $this->height) {
            return '—';
        }

        return "{$this->width} x {$this->height} px";
    }

    /**
     * The file size in KB, or a dash when unknown.
     */
    public function getSizeLabelAttribute(): string
    {
        if (! $this->file_size) {
            return '—';
        }

        return $this->file_size >= 1048576
            ? number_format($this->file_size / 1048576, 1) . ' MB'
            : max(1, (int) round($this->file_size / 1024)) . ' KB';
    }

    /**
     * The slider wants width greater than height. Anything else is reported so
     * the admin can replace it.
     */
    public function isLandscape(): bool
    {
        return $this->width !== null && $this->height !== null && $this->width > $this->height;
    }

    public function getOrientationAttribute(): string
    {
        if (! $this->width || ! $this->height) {
            return 'unknown';
        }

        if ($this->width > $this->height) {
            return 'landscape';
        }

        return $this->width === $this->height ? 'square' : 'portrait';
    }
}
