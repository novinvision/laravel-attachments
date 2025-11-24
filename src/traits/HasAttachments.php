<?php

namespace NovinVision\Attachments\Traits;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use NovinVision\Attachments\Models\Attachment;
use ValueError;

/**
 *  * @property Attachment[]|Collection $attachments
 */
trait HasAttachments
{

    public function bootHasAttachments()
    {
        static::deleted(function (Model $deletedModel) {
            Attachment::query()->whereMorphedTo('rel', $deletedModel)->each(function (Attachment $attachment) {
                $attachment->delete();
            });
        });
    }

    public function getAttachmentDiskName(): string
    {
        return config('attachments.disk', 'public');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'rel');
    }

    public function attachment(): MorphOne
    {
        return $this->morphOne(Attachment::class, 'rel')->latest();
    }

    /**
     * @throws Exception
     * @property UploadedFile[] $files
     */
    public function addAttachments(array $files, $disk = null): int
    {
        if (!$disk) $disk = $this->getAttachmentDiskName();

        $attachments = [];
        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) {
                throw new Exception("file not instanceof UploadedFile");
            }

            $storePath = $file->store($this->attachmentsDiskBasePath(), $disk);
            if ($storePath) $attachments[] = [
                'rel_type' => self::class,
                'rel_id' => $this->getKey(),
                'disk' => $disk,
                'orig_name' => $file->getClientOriginalName(),
                'path' => $storePath,
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
            ];
        }

        return $this->attachments()->insert($attachments);
    }

    public function addAttachment(UploadedFile $file, $disk = null)
    {
        if (!$disk) $disk = $this->getAttachmentDiskName();
        $storePath = $file->store($this->attachmentsDiskBasePath(), $disk);
        if (!$storePath) return false;

        return $this->attachments()->create([
            'rel_type' => self::class,
            'rel_id' => $this->getKey(),
            'disk' => $disk,
            'orig_name' => $file->getClientOriginalName(),
            'path' => $storePath,
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ]);
    }

    protected function attachmentsDiskBasePath(): ?string
    {
        $dateFormat = config('attachments.date_format');
        return $dateFormat ? date($dateFormat) : null;
    }
}
