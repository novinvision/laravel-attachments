<?php

namespace NovinVision\Attachments\Traits;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
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
        return 'attachments';
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'rel');
    }

    /**
     * @throws Exception
     */
    public function addAttachments(array $files, $path = null, $disk = null): int
    {
        if (!$disk) $disk = $this->getAttachmentDiskName();

        $attachments = [];
        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) {
                throw new Exception("file not instanceof UploadedFile");
            }

            try {
                $storePath = $file->storeAs($path, $file->getClientOriginalName(), $disk);
                if (!$storePath) {
                    throw new Exception("unable to store file {$file->path()} in disk {$disk}");
                }
            }catch (ValueError $exception){
                throw new Exception("unable to store file path {$path}: {$exception->getMessage()} path:" . print_r($file->path(), true));
            }

            $attachments[] = [
                'rel_type' => self::class,
                'rel_id' => $this->getKey(),
                'disk' => $disk,
                'path' => $storePath,
            ];
        }

        return $this->attachments()->insert($attachments);
    }
}
