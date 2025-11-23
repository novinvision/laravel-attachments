<?php

namespace NovinVision\Attachments\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

/**
 * @property ?string $name
 * @property ?string $orig_name
 * @property string $path
 * @property string $disk
 * @property Model $rel
 * @method Attachment create(array $array)
 */

class Attachment extends Model
{
    public $timestamps = false;

    protected $appends = [
        'mime_type',
        'url',
    ];

    protected $fillable = [
        'rel_type',
        'rel_id',
        'name',
        'orig_name',
        'disk',
        'path',
        'size',
    ];

    protected $hidden = [
        'rel_type',
        'rel_id',
    ];

    protected static function booted()
    {
        static::deleting(function (Attachment $attachment) {
            Storage::disk($this->disk)->delete($this->path);
        });
    }

    public function rel(): MorphTo
    {
        return $this->morphTo('rel');
    }

    public function mimeType(): bool|string
    {
        return ($this->path && $this->disk) ? Storage::disk($this->disk)->mimeType($this->path) : '';
    }

    public function getMimeTypeAttribute(): string
    {
        return $this->mimeType();
    }

    public function download(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return Storage::disk($this->disk)->download($this->path);
    }

}
