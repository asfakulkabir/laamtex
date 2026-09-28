<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    protected $fillable = ['title', 'image', 'video', 'video_type', 'link', 'sort_order', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function isVideo(): bool
    {
        return !empty($this->video);
    }

    public function isYoutubeVideo(): bool
    {
        return $this->video_type === 'youtube' && !empty($this->video);
    }

    public function isUploadedVideo(): bool
    {
        return $this->video_type === 'upload' && !empty($this->video);
    }

    public function getYoutubeIdAttribute(): ?string
    {
        if (!$this->isYoutubeVideo()) {
            return null;
        }

        $url = $this->video;
        $patterns = [
            '~(?:youtube\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)[\w-]{11}~',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                if (preg_match('/[\w-]{11}$/', $matches[0], $id)) {
                    return $id[0];
                }
            }
        }

        return null;
    }
}