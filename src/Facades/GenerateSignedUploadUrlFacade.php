<?php

namespace Livewire\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @internal
 *
 * @method static string forLocal()
 * @method static string forS3($file, $visibility = 'private')
 * @method static string signedRoute($name, $expiration, $parameters = [])
 *
 * @see \Livewire\Features\SupportFileUploads\GenerateSignedUploadUrl
 */
class GenerateSignedUploadUrlFacade extends Facade
{
    public static function getFacadeAccessor()
    {
        return \Livewire\Features\SupportFileUploads\GenerateSignedUploadUrl::class;
    }
}
