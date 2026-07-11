<?php

namespace Livewire\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @internal
 *
 * @method static array plan($fileInfo)
 * @method static string complete($fingerprint)
 * @method static void abort($fingerprint)
 *
 * @see \Livewire\Features\SupportFileUploads\S3MultipartUpload
 */
class S3MultipartUploadFacade extends Facade
{
    public static function getFacadeAccessor()
    {
        return \Livewire\Features\SupportFileUploads\S3MultipartUpload::class;
    }
}
