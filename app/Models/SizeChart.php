<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SizeChart extends Model
{
    use HasFactory;

    /**
     * Size charts are measurement tables, so they are read at a glance on a
     * phone. Portrait artwork taller than this gets shrunk to fit the modal
     * and the numbers become unreadable.
     */
    public const RECOMMENDED_WIDTH = 800;

    public const RECOMMENDED_HEIGHT = 1000;

    protected $fillable = [
        'title',
        'image',
        'width',
        'height',
        'file_size',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'width' => 'integer',
        'height' => 'integer',
        'file_size' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted()
    {
        // Deleting a chart that products still point at would leave those
        // products holding a foreign key to a row that no longer exists, so the
        // column is nullOnDelete. The file still has to go with the row.
        static::deleted(function (SizeChart $sizeChart) {
            if ($sizeChart->image && ! Media::isLibraryPath($sizeChart->image)) {
                Storage::disk('public')->delete($sizeChart->image);
            }
        });

        static::updating(function (SizeChart $sizeChart) {
            $original = $sizeChart->getOriginal('image');

            if ($original && $original !== $sizeChart->image && ! Media::isLibraryPath($original)) {
                Storage::disk('public')->delete($original);
            }
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
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
     * "800 x 1000 px", or a dash when the dimensions were never recorded.
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
}
