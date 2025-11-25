<?php

namespace NovinVision\Attachments\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

/**
 * @property ?string $rel_type
 * @property ?string $rel_id
 * @property ?string $orig_name
 * @property string $path
 * @property string $disk
 * @property string $mime_type
 * @property integer $size
 * @property Model $rel
 * @method Attachment create(array $array)
 */

class Attachment extends Model
{
    protected $fillable = [
        'rel_type',
        'rel_id',
        'orig_name',
        'disk',
        'path',
        'size',
        'mime_type',
    ];

    protected $hidden = [
        'rel_type',
        'rel_id',
        'disk',
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

    public function download(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return Storage::disk($this->disk)->download($this->path);
    }

}
